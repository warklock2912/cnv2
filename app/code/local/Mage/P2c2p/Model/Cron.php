<?php
require_once Mage::getBaseDir('lib') . DS . 'PaymentActionP2c2p' . DS . 'pkcs7.php';
require_once Mage::getBaseDir('lib') . DS . 'PaymentActionP2c2p' . DS . 'HTTP.php';
class Mage_P2c2p_Model_Cron
{
    public function recheckAction()
    { 
        $fromDate = date('Y-m-d H:i:s', strtotime('-1 day'));
        $toDate = date('Y-m-d H:i:s', strtotime('-10 minutes'));

        $orderCollection = Mage::getModel('sales/order')->getCollection()
            // ->addAttributeToFilter('transaction_ref', array('nin' => null))
            // ->addAttributeToFilter('statuscode', array('nin' => array('A','000','00',null)))
            ->addAttributeToFilter('status', array('in' => array('Pending_2C2P', 'store_pickup')))
            // ->addAttributeToFilter('entity_id', array('eq' => '68597'))
            ->addAttributeToFilter('created_at', ['from' => $fromDate, 'to' => $toDate]);
            // ->addAttributeToFilter('created_at', array('gt' => '2019-09-21'));
            // echo $orderCollection->getSelect()->__toString(); die;

        Mage::log('[SQL]|'.$orderCollection->getSelect()->__toString(), null, '2C2P-INQUIRY-'.date('Ymd').'.log', true);
        Mage::log('[COUNT_ORDER]|'.count($orderCollection), null, '2C2P-INQUIRY-'.date('Ymd').'.log', true);

        if (count($orderCollection) > 0) {
            $merchantID = Mage::getStoreConfig('payment/p2c2p/merchantid', Mage::app()->getStore());
            $secretKey = Mage::getStoreConfig('payment/p2c2p/apisecretekeytest', Mage::app()->getStore());
            $version = "3.4";
            $processType = "I";

            Mage::log('===== START =====', null, '2C2P-INQUIRY-'.date('Ymd').'.log', true);
            foreach ($orderCollection as $order) {
                try {
                    if (!$order->canInvoice()) {
                        continue;
                    }
                    
                    if (
                        $order->getPayment()->getMethod() == 'p2c2p_onsite_internet_banking' || 
                        $order->getPayment()->getMethod() == 'p2c2p' || 
                        $order->getPayment()->getMethod() == 'p2c2predirect' || 
                        $order->getPayment()->getMethod() == 'crystal_twoctwop'
                    ) {
                        Mage::log('===============================================', null, '2C2P-INQUIRY-'.date('Ymd').'.log', true);
                        Mage::log('[ORDER_NO]|'.$order->getIncrementId(), null, '2C2P-INQUIRY-'.date('Ymd').'.log', true);

                        $invoiceNo = $order->getIncrementId();
                        $stringToHash = $version . $merchantID . $processType . $invoiceNo;
                        $hash = strtoupper(hash_hmac('sha1', $stringToHash, $secretKey, false));
                        $xml = "<PaymentProcessRequest>
            <version>$version</version> 
            <merchantID>$merchantID</merchantID>
            <processType>$processType</processType>
            <invoiceNo>$invoiceNo</invoiceNo> 
            <hashValue>$hash</hashValue>
            </PaymentProcessRequest>";
                        $pkcs7 = new pkcs7();

                        $crtfile = Mage::getStoreConfig('payment/p2c2p/crt_file', Mage::app()->getStore());
                        $pemfile = Mage::getStoreConfig('payment/p2c2p/pem_file', Mage::app()->getStore());
                        $crt_file_2c2p_request = Mage::getStoreConfig('payment/p2c2p/crt_file_2c2p_request', Mage::app()->getStore());
                        $merchantPassword = Mage::getStoreConfig('payment/p2c2p/merchant_private_password', Mage::app()->getStore());

                        $payload = $pkcs7->encrypt($xml, $crt_file_2c2p_request); //Encrypt payload
                        $http = new HTTP();
                        $url = $this->getPaymentAction();
                        $response = $http->post($url, "paymentRequest=" . $payload);

                        $response = $pkcs7->decrypt($response, $crtfile, $pemfile, $merchantPassword);

                        // Validate response Hash
                        $resXml = simplexml_load_string($response);

                        $stringToHash = $resXml->version . $resXml->respCode . $resXml->processType . $resXml->invoiceNo . $resXml->amount . $resXml->status . $resXml->approvalCode . $resXml->referenceNo . $resXml->transactionDateTime . $resXml->paidAgent . $resXml->paidChannel . $resXml->maskedPan . $resXml->eci . $resXml->paymentScheme . $resXml->processBy . $resXml->refundReferenceNo . $resXml->userDefined1 . $resXml->userDefined2 . $resXml->userDefined3 . $resXml->userDefined4 . $resXml->userDefined5;

                        $responseMessage = "Process Inquiry By Cron <br>";
                        $responseMessage .= "version = " . $resXml->version . "<br>";
                        $responseMessage .= "respCode = " . $resXml->respCode . "<br>";
                        $responseMessage .= "processType = " . $resXml->processType . "<br>";
                        $responseMessage .= "invoiceNo = " . $resXml->invoiceNo . "<br>";
                        $responseMessage .= "amount = " . $resXml->amount . "<br>";
                        $responseMessage .= "status = " . $resXml->status . "<br>";
                        $responseMessage .= "approvalCode = " . $resXml->approvalCode . "<br>";
                        $responseMessage .= "referenceNo = " . $resXml->referenceNo . "<br>";
                        $responseMessage .= "transactionDateTime = " . $resXml->transactionDateTime . "<br>";
                        $responseMessage .= "paidAgent = " . $resXml->paidAgent . "<br>";
                        $responseMessage .= "paidChannel = " . $resXml->paidChannel . "<br>";
                        $responseMessage .= "maskedPan = " . $resXml->maskedPan . "<br>";
                        $responseMessage .= "eci = " . $resXml->eci . "<br>";
                        $responseMessage .= "paymentScheme = " . $resXml->paymentScheme . "<br>";
                        $responseMessage .= "processBy = " . $resXml->processBy ."<br>";
                        $responseMessage .= "refundReferenceNo = " . $resXml->refundReferenceNo . "<br>";
                        $responseMessage .= "userDefined1 = " . $resXml->userDefined1 . "<br>";
                        $responseMessage .= "userDefined2 = " . $resXml->userDefined2 . "<br>";
                        $responseMessage .= "userDefined3 = " . $resXml->userDefined3 . "<br>";
                        $responseMessage .= "userDefined4 = " . $resXml->userDefined4 . "<br>";
                        $responseMessage .= "userDefined5 = " . $resXml->userDefined5;

                        $responseMessageShort = 'Process Inquiry By Cron <br>'
                            .'respCode : '.$resXml->respCode.'<br>'
                            .'status : '.$resXml->status
                        ;

                        Mage::log('[RESPONSE]|'.$responseMessage, null, '2C2P-INQUIRY-'.date('Ymd').'.log', true);
                        Mage::log('[RESP_CODE]|'.$resXml->respCode, null, '2C2P-INQUIRY-'.date('Ymd').'.log', true);

                        $responseHash = strtoupper(hash_hmac('sha1', $stringToHash, $secretKey, false));
                        if ($resXml->hashValue == strtolower($responseHash)) {

                            // Success
                            if ($resXml->respCode == '00') {
                                Mage::log('[RESP_STATUS]|'.$resXml->status.'|'.$this->getInquiryStatusMessage($resXml->status), null, '2C2P-INQUIRY-'.date('Ymd').'.log', true);

                                // Status code
                                // A : Approved.
                                // S : Settled.
                                if ($resXml->status == 'A' || $resXml->status == 'S')
                                {
                                    Mage::log('[ORDER_STATE]|'.$order->getState(), null, '2C2P-INQUIRY-'.date('Ymd').'.log', true);
                                    Mage::log('[ORDER_STATUS]|'.$order->getStatus(), null, '2C2P-INQUIRY-'.date('Ymd').'.log', true);
                                    
                                    $order->setStatuscode($resXml->status);
                                    $order->addStatusToHistory($order->getStatus(), $responseMessage, false);
                                    // $order->addStatusHistoryComment("Process Inquiry By Cron" . $responseMessage);
                                    $order->save();

                                    Mage::log('[PROC]|Create Invoice', null, '2C2P-INQUIRY-'.date('Ymd').'.log', true);

                                    $payment = $order->getPayment();
                                    $invoice = $order->prepareInvoice()->register();
                                    $payment->setCreatedInvoice($invoice)
                                        ->setIsTransactionClosed(false)
                                        ->setIsTransactionPending(true)
                                        ->addTransaction(
                                            Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
                                            $invoice,
                                            false,
                                            Mage::helper('p2c2p')->__('Capturing an amount of %s ', $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal()))
                                        );

                                    $order->addRelatedObject($invoice);

                                    $items = array();
                                    foreach ($order->getAllItems() as $item) {
                                        $items[$item->getId()] = $item->getQtyOrdered();
                                    }
                                    $invoiceId = Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(), $items, null, false, true);
                                    #capture the invoice
                                    Mage::getModel('sales/order_invoice_api')->capture($invoiceId);
                                }
                                // Cancel order
                                elseif ($resXml->status == 'PF')
                                {
                                    if ($order->canCancel()) {
                                        Mage::log('[PROC]|[CANCEL_ORDER]|Start', null, '2C2P-INQUIRY-'.date('Ymd').'.log', true);
                                        // $order->setStatuscode($resXml->status);
                                        // $order->cancel();
                                        $order->addStatusToHistory($order->getStatus(), $responseMessageShort, false);
                                        // $order->addStatusHistoryComment("Process Inquiry By Cron" . $responseMessage);
                                        // $order->save();
                                        Mage::log('[PROC]|[CANCEL_ORDER]|End', null, '2C2P-INQUIRY-'.date('Ymd').'.log', true);
                                    } else {
                                        Mage::log('[PROC]|Can not cancel order', null, '2C2P-INQUIRY-'.date('Ymd').'.log', true);
                                        continue;
                                    }
                                }
                                // Nothing action
                                else 
                                {
                                    // $resXml->respCode = 31,32,33,34,35,39,40,41,42,43,44,45,46,47,48
                                    Mage::log('[NO_ACTION]|'.$order->getIncrementId(), null, '2C2P-INQUIRY-'.date('Ymd').'.log', true);

                                    // add history
                                    $order->addStatusToHistory($order->getStatus(), $responseMessageShort, false)->save();
                                    continue;
                                }
                            }
                            // Cancel order
                            elseif ($this->doCancel($resXml->respCode)) {
                                $order->addStatusToHistory($order->getStatus(), $responseMessageShort, false)->save();
                            // Nothing action
                            } else {
                                // $resXml->respCode = 31,32,33,34,35,39,40,41,42,43,44,45,46,47,48
                                Mage::log('[NO_ACTION]|'.$order->getIncrementId(), null, '2C2P-INQUIRY-'.date('Ymd').'.log', true);

                                // add history
                                $order->addStatusToHistory($order->getStatus(), $responseMessageShort, false)->save();
                            }

                        } else {
                            Mage::log('[PROC]|Hash not valid', null, '2C2P-INQUIRY-'.date('Ymd').'.log', true);
                            // invalid response
                            throw new Exception('Hash not valid');
                        }
                    }

                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
            Mage::log('===== END =====', null, '2C2P-INQUIRY-'.date('Ymd').'.log', true);
        }
    }

    private function getPaymentAction()
    {
        $url = "https://t.2c2p.com/PaymentActionV2/PaymentAction.aspx";
        $sandboxUrl = "https://demo2.2c2p.com/2C2PFrontend/PaymentActionV2/PaymentAction.aspx";

        $test_mode = Mage::getStoreConfig('payment/p2c2p/gatewayurl', Mage::app()->getStore());

        if ($test_mode) {
            return $sandboxUrl;
        }
        else {
            return $url;
        }
    }

    private function doCancel($code){
        // 00 : Success
        // 01 : Stored card ID cannot be found
        // 02 : Invalid Request
        // 03 : Invalid Merchant ID
        // 04 : Invalid Stored Card Unique ID
        // 05 : Invalid Customer Email
        // 10 : Missing Compulsory Values
        // 11 : Request validation failed.
        // 12 : Transaction status is not valid to perform your action.
        // 13 : Invalid hash value.
        // 14 : Invalid merchant id.
        // 15 : Invalid invoice no.
        // 16 : Requested transaction doesn't exist.
        // 17 : Request type is invalid.
        // 18 : Invalid Action Amount.
        // 21 : Void not allowed.
        // 25 : Void failed.
        // 30 : Unable to refund more than transaction amount.
        // 31 : Settlement not allowed.
        // 32 : Settlement is not required.
        // 33 : Partial settlement not allowed.
        // 34 : Settlement rejected.
        // 35 : Settlement failed.
        // 39 : Transaction is already settled.
        // 40 : Refund amount is more than transaction amount.
        // 41 : Refund not allowed.
        // 42 : Refund pending.
        // 43 : Partial Refund not allowed.
        // 44 : Refund rejected.
        // 45 : Refund failed.
        // 46 : Insufficient funds to perform refund.
        // 47 : Sub Merchant refund amount is more than transaction amount.
        // 48 : Sub merchant has insufficient funds to perform refund.
        // 96 : Unable to decrypt.
        // 97 : Process is not supported.
        // 98 : Request is not available
        // 99 : Unable to complete the request.

        $resultCode = ['01','02','03','04','05','10','11','12','13','14','15','16','17','18','21','25','30','96','97','98','99'];
        // $resultCode = ['31','32','33','34','35','39','40','41','42','43','44','45','46','47','48'];
        if(in_array($code, $resultCode)){
            return true;
        }
        return false;
    }

    private function getInquiryStatusMessage($code){
        $responseCode = [
            'A'     => 'Approved.',
            'AP'    => 'Approval Pending (APM).',
            'AE'    => 'Approved after Expired (APM).',
            'AL'    => 'Approved with less amount (APM).',
            'AM'    => 'Approved with more amount (APM).',
            'PF'    => 'Payment Failed.',
            'AR'    => 'Authentication Rejected (MPI Reject).',
            'FF'    => 'Fraud Rule Rejected.',
            'IP'    => 'Rejected (Invalid Promotion).',
            'ROE'   => 'Rejected (Routing Rejected).',
            'RP'    => 'Refund Pending.',
            'RF'    => 'Refund confirmed.',
            'RR'    => 'Refund Rejected.',
            'RR1'   => 'Refund Rejected – insufficient balance.',
            'RR2'   => 'Refund Rejected – invalid bank information.',
            'RR3'   => 'Refund Rejected – bank account mismatch.',
            'RS'    => 'Ready for Settlement.',
            'S'     => 'Settled',
            'T'     => 'Credit Adjustment',
            'V'     => 'Voided / Canceled',
            'VP'    => 'Void Pending'
        ];
        return $responseCode[(string)$code];
    }
}