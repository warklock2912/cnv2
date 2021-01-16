<?php

class Tigren_Kpayment_Credit_ProcessController extends Mage_Core_Controller_Front_Action
{
    public function resultAction()
    {
        try {
            $data = $this->getRequest()->getParams();
            /** @var Tigren_Kpayment_Helper_Data $kHelper **/
            $kHelper = Mage::helper('kpayment');
            /** @var Tigren_Kpayment_Model_Charge $charge **/
            $charge = Mage::getModel('kpayment/charge');
            $createCharge = $charge->createKpaymentCreditCharge($data);

            $order = Mage::getModel('checkout/session')->getLastRealOrder();

            $payment = $order->getPayment();

            $this->saveReferencePayment($payment, $order, $createCharge, $data['token']);

            $kHelper->logAPI('[CHARGE RESPONSE Kpayment KpaymentCode]', 'credit');
            $kHelper->logAPI(array($createCharge), 'credit');
            $kHelper->logAPI('===== END =====', 'credit');

            if(!empty($createCharge['id']) && $createCharge['status'] == 'success'){
                if (!empty($createCharge['redirect_url'])){
                    $this->_redirectUrl($createCharge['redirect_url']);
                }
                else {
                    $headerPrivate = array(
                        'Content-Type: ' . 'application/json; charset=UTF-8',
                        'x-api-key: ' . $kHelper->getSecretKey()
                    );

                    $objectId = $createCharge['id'];

                    $store = $order->getStore();

                    if (!$order || !$order->getId()) {
                        return $this->_redirect('checkout/onepage/failure', array('_secure'=>true));
                    }

                    $payment = $order->getPayment();

                    $paymentMethod = $payment->getMethod();
                    if ($paymentMethod == 'kpayment_credit') {

                        $url = $this->getUrl('kpayment_credit') . '/charge/' . $objectId;
                        $kHelper->logAPI('[CALLBACK REQUEST Kpayment KpaymentCode]', 'credit');
                        $kHelper->logAPI($url, 'credit');

                        $ch = curl_init();

                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerPrivate);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $response = curl_exec($ch);
                        $response = json_decode($response, true);

                        $responseMessage = "- **** Reviced response from Kpayment KpaymentCode Callback<br>";

                        if ($response && is_array($response)) {
                            foreach ($response as $key => $value) {
                                if (is_array($value)) {
                                    foreach ($value as $childKey => $childValue) {
                                        $responseMessage .= $key . '[' . $childKey . ']' . ' = ' . $childValue . '<br>';
                                    }
                                } else {
                                    $responseMessage .= $key . ' = ' . $value . '<br>';
                                }
                            }
                        }
                        else {
                            foreach ($createCharge as $key => $value) {
                                if (is_array($value)) {
                                    foreach ($value as $childKey => $childValue) {
                                        $responseMessage .= $key . '[' . $childKey . ']' . ' = ' . $childValue . '<br>';
                                    }
                                } else {
                                    $responseMessage .= $key . ' = ' . $value . '<br>';
                                }
                            }
                        }

                        $kHelper->logAPI('[CALLBACK RESPONSE Kpayment KpaymentCode]', 'credit');
                        $kHelper->logAPI(array($response), 'credit');
                        $kHelper->logAPI('===== END =====', 'credit');

                        if (!empty($response['status']) && $response['status'] === 'success') {
                            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
                            $order->addStatusToHistory($kHelper->getOrderStatusPaidCredit(), $responseMessage);
                            $order->save();
                            if ($kHelper->getCreateAutoInvoiceCredit()) {
                                if ($order->canInvoice()) {
                                    $invoice = $order->prepareInvoice();
                                    $invoice->register();
                                    $payment = $order->getPayment();
                                    $payment->setCreatedInvoice($invoice)
                                        ->setIsTransactionClosed(false)
                                        ->setIsTransactionPending(true)
                                        ->addTransaction(
                                            Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH,
                                            $invoice,
                                            false,
                                            $this->__('Authorizing an amount of %s ', $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal()))
                                        );
                                    $order->addRelatedObject($invoice);
                                }
                            }

                            $this->_updateInquiryStatus($createCharge['id'], $order->getIncrementId(), $createCharge['status']);

                            return $this->_redirect('checkout/onepage/success', array('_secure' => true));
                        }
                        elseif(!empty($response['status']) && $response['status'] === 'pending') {
                            $history = $order->addStatusHistoryComment($responseMessage);
                            $history->setIsVisibleOnFront(false);
                            $history->setIsCustomerNotified(false);
                            $history->save();

                            Mage::getSingleton('core/session')->addError('Sorry, the credit card cannot be authorized for this transaction, please change your credit card or contact the issued bank.');
                            return $this->_redirect('checkout/onepage/success', array('_secure' => true));
                        }
                        else {
                            $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
                            $order->setState($state)
                                ->addStatusToHistory(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, $responseMessage)
                                ->save();

                            Mage::getSingleton('core/session')->addError('Sorry, the credit card cannot be authorized for this transaction, please change your credit card or contact the issued bank.');
                            return $this->_redirect('checkout/onepage/success', array('_secure' => true));
                        }
                    }
                }
            }
            return $this->getResponse()->setBody($createCharge);
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            return $this->_redirect('checkout/cart', array('_secure' => true));
        }
    }

    private function getUrl($id = '')
    {
        /** @var Tigren_Kpayment_Helper_Data $kHelper **/
        $kHelper = Mage::helper('kpayment');
        return $kHelper->getConfigData($id,'api_base_url');
    }

    public function saveReferencePayment($payment, $order, $charge, $token)
    {
        if (!isset($charge['id'])) {
            return $this;
        }
        $payment->setAdditionalInformation('charge_id', $charge['id']);
        $payment->setAdditionalInformation('transaction_state', $charge['transaction_state']);
        $payment->setAdditionalInformation('status', $charge['status']);
        $payment->setAdditionalInformation('source', json_encode($charge['source'], true));
        $payment->setAdditionalInformation('authen_url', $charge['redirect_url']);
        $payment->save();

        /** @var Tigren_Kpayment_Model_Credit_Reference $reference **/
        $reference = Mage::getModel('kpayment/credit_reference');
        $reference->setData('token_id', $token);
        $reference->setData('method', 'Kpaymentredirect');
        $reference->setData('order_increment', $order->getIncrementId());
        $reference->setData('charge_id', $charge['id']);
        $reference->setData('object', $charge['object']);
        $reference->setData('amount', $charge['amount']);
        $reference->setData('currency', $charge['currency']);
        $reference->setData('transaction_state', $charge['transaction_state']);
        $reference->setData('source_id', $charge['source']['id']);
        $reference->setData('source_object', $charge['source']['object']);
        $reference->setData('source_brand', $charge['source']['brand']);
        $reference->setData('source_card_masking', $charge['source']['card_masking']);
        $reference->setData('created', $charge['created']);
        $reference->setData('status', $charge['status']);
        $reference->setData('livemode', $charge['livemode'] ? 1 : 0);
        $reference->setData('failure_code', $charge['failure_code']);
        $reference->setData('failure_message', $charge['failure_message']);
        $reference->setData('authen_url', $charge['redirect_url']);
        $reference->setData('settlement_info', $charge['settlement_info']);
        $reference->setData('refund_info', $charge['refund_info']);
        $reference->setData('response_at', date('Y-m-d H:i:s'));
        $reference->save();
    }

    protected function _updateInquiryStatus($chargeId, $orderIncrement, $inquiryStatus = 'default')
    {
        try{
            /** @var Tigren_Kpayment_Helper_Data $kHelper **/
            $kHelper = Mage::helper('kpayment');

            $write = $kHelper->writeAdapter();

            $query = "UPDATE kpayment_credit_reference SET inquiry_status = :inquiryStatus, inquiry_date = NOW() WHERE charge_id = :chargeId AND order_increment = :orderIncrement;";
            $write->query($query, array(
                'chargeId' => $chargeId,
                'orderIncrement' => $orderIncrement,
                'inquiryStatus' => $inquiryStatus
            ));
        }
        catch(Exception $e){
            return array(
                'code' => '500',
                'message' => $e->getMessage()
            );
        }
        return array(
            'code' => '200',
            'message' => 'success'
        );
    }
}
