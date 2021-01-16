<?php

// app/code/local/payment/P2c2p/controllers/PaymentController.php
class Mage_P2c2p_PaymentController extends Mage_Core_Controller_Front_Action
{
    public function redirectAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('Mage_Core_Block_Template', 'p2c2p', array('template' => 'p2c2p/redirect.phtml'));
        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
    }
    public function ibankingAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('Mage_Core_Block_Template', 'p2c2p_ibanking', array('template' => 'p2c2p/ibankingsubmit.phtml'));
        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
    }

    public function requestonsiteAction()
    {
        /* Create helper class object */
        $objRequestHelper = Mage::helper('P2c2p/Request');
        /* get order detail */
        $_order = new Mage_Sales_Model_Order();
        $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $_order->loadByIncrementId($orderId);

        $currencyType = $_order->getBaseCurrencyCode();
        $amount = round($_order->getGrandTotal(), 2);
        $amount = $objRequestHelper->p2c2p_get_amount_by_currency_type($currencyType, $amount);

        //Merchant's account information
        $merchantID = Mage::getStoreConfig('payment/p2c2p/merchantid', Mage::app()->getStore());
        $secretKey = Mage::getStoreConfig('payment/p2c2p/apisecretekeytest', Mage::app()->getStore());


        //Transaction Information
        $desc = $_order->getIncrementId();
        // $uniqueTransactionCode = $_order->getId() . time();
        $uniqueTransactionCode = $_order->getIncrementId();
        $currencyCode = "764";
        $amt = $amount;
        $panCountry = "TH";

        //Customer Informationx
        $cardholderName = $_POST['holder_name'];
        $cardType = $_POST['card_type'];
        //Encrypted card data
        $encCardData = $_POST['encryptedCardInfo'];
        Mage::getSingleton('checkout/session')->setData('card_type', $cardType);
        //Retrieve card information for merchant use if needed
        $maskedCardNo = $_POST['maskedCardInfo'];
        $expMonth = $_POST['expMonthCardInfo'];
        $expYear = $_POST['expYearCardInfo'];
        //Payment Options
        if (isset($_POST['payment']['is_saved_card'])) //check if enable store card or not
        {
            $storeCard = "Y";
        } else {
            $storeCard = "N";
        }
        $request3DS = "Y";        //Enable / Disable Tokenization

        ///pay with stored card
        $storeCardUniqueID = null;

        if ($_POST['payment']['custom_field_one'] !== 'new_2c2p_card') {
            $customer_id = Mage::getSingleton('customer/session')->getId();

            $p2c2pTokenModel = Mage::getModel('p2c2p/token');

            if (!$p2c2pTokenModel) {
                die("2C2P Expected Model not available.");
            }
            /* Ken's code : dont need to foreach collection to filter $storeCardUniqueID */
            // $customer_data = $p2c2pTokenModel->getCollection()->addFieldToFilter('user_id', $customer_id);
            $p2c2pId = $_POST['payment']['custom_field_one'];
            $p2c2pTokenModel->load($p2c2pId);
            if($p2c2pTokenModel->getId()){
                $storeCardUniqueID = $p2c2pTokenModel->getData('stored_card_unique_id');
            }
            // foreach ($customer_data as $key => $value) {
            //     if ($value->getData('p2c2p_id') == $_POST['payment']['custom_field_one']) {
            //         $storeCardUniqueID = $value->getData('stored_card_unique_id');
            //     }
            // }
        }


        //Request Information
        $version = "9.9";

        //Construct payment request message

        if ($storeCardUniqueID == null) //request with new card
        {
            $xml = "<PaymentRequest>
		<merchantID>$merchantID</merchantID>
		<uniqueTransactionCode>$uniqueTransactionCode</uniqueTransactionCode>
		<desc>$desc</desc>
		<amt>$amt</amt>
		<currencyCode>$currencyCode</currencyCode>
		<panCountry>$panCountry</panCountry>
		<cardholderName>$cardholderName</cardholderName>
		<request3DS>$request3DS</request3DS>
        <storeCard>$storeCard</storeCard>
		<encCardData>$encCardData</encCardData>
		</PaymentRequest>";
        } else  //request with stored card
        {
            $xml = "<PaymentRequest>
		<merchantID>$merchantID</merchantID>
		<uniqueTransactionCode>$uniqueTransactionCode</uniqueTransactionCode>
		<desc>$desc</desc>
		<amt>$amt</amt>
		<currencyCode>$currencyCode</currencyCode>
		<storeCardUniqueID>$storeCardUniqueID</storeCardUniqueID>
		<panCountry>$panCountry</panCountry>
		<cardholderName>$cardholderName</cardholderName>
		<request3DS>$request3DS</request3DS>
         <encCardData>$encCardData</encCardData>
		</PaymentRequest>";
        }


        $paymentPayload = base64_encode($xml); //Convert payload to base64
        $signature = strtoupper(hash_hmac('sha256', $paymentPayload, $secretKey, false));
        $payloadXML = "<PaymentRequest>
           <version>$version</version>
           <payload>$paymentPayload</payload>
           <signature>$signature</signature>
           </PaymentRequest>";

        $payload = array(
            'paymentRequest' => base64_encode($payloadXML),
        );
        echo $this->charge($payload);
    }

    public function charge($payload)
    {
        Mage::log(print_r($payload, 1), null, '2c2p-requestonsite.log', true);
        $chargeResponse = $this->requestHTTP($payload);
        Mage::log(print_r($chargeResponse, 1), null, '2c2p-requestonsite.log, true');

        return $chargeResponse;
    }

    public function requestHTTP($payload)
    {
        $url = $this->getHost();
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;

    }

    public function reponseonsiteAction()
    {
        try {
            $response = $_REQUEST["paymentResponse"];
            //Decode response with base64
            $reponsePayLoadXML = base64_decode($response);
            $cardType = Mage::getSingleton('checkout/session')->getData('card_type');
            Mage::getSingleton('checkout/session')->unsetData('card_type');
            //Parse ResponseXML
            $xmlObject = simplexml_load_string($reponsePayLoadXML) or die("Error: Cannot create object");

            //Decode payload with base64 to get the Reponse
            $payloadxml = base64_decode($xmlObject->payload);

            //Get the signature from the ResponseXML
            $signaturexml = $xmlObject->signature;

            $secretKey = $secretKey = Mage::getStoreConfig('payment/p2c2p/apisecretekeytest', Mage::app()->getStore());    //Get SecretKey from 2C2P PGW Dashboard

            //Encode the payload
            $base64EncodedPayloadResponse = base64_encode($payloadxml);
            //Generate signature based on "payload"
            $signatureHash = strtoupper(hash_hmac('sha256', $base64EncodedPayloadResponse, $secretKey, false));

            //Compare the response signature with payload signature with secretKey
            $payloadxml = simplexml_load_string($payloadxml, "SimpleXMLElement", LIBXML_NOCDATA);
            $json = json_encode($payloadxml);
            $arrayReponse = json_decode($json, true);
            Mage::log('[RESPONSE]|'.print_r($arrayReponse, true), null, '2c2p_Response_'.date('Ymd').'.log', true);
            
            $payment_status = @$arrayReponse['status'] ?: '';
            $transaction_ref = @$arrayReponse['uniqueTransactionCode'] ?: 0;
            $approval_code = @$arrayReponse['approvalCode'] ?: '';
            
            // $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
            if (empty($transaction_ref)) {
                Mage::getSingleton('core/session')->addError($this->__('Sorry, Not found order.'));

                return $this->_redirect('checkout/onepage/success');
            }

            if ($signaturexml == $signatureHash) {
                $order = Mage::getModel('sales/order')->loadByIncrementId($transaction_ref);
                if ($arrayReponse['failReason'] != null){
                    $order->setStatus('pending');
                    $order->save();
                }
                if ($arrayReponse['respCode'] == "00") {
                    try {
                        if (Mage::getSingleton('customer/session')->isLoggedIn() && $arrayReponse['storeCardUniqueID']) {
                            //save card token to customer
                            $customer = Mage::getSingleton('customer/session')->getCustomer();
                            $customer->setData('card_token_2c2p', $arrayReponse['storeCardUniqueID']);
                            $customer->save();

                            $customer_id = $customer->getId();
                            $isFouned = false;

                            //Fatch data from database by customer ID.
                            $p2c2pTokenModel = Mage::getModel('p2c2p/token');

                            if (!$p2c2pTokenModel) {
                                throw Exception('2C2P Expected Model not available.');
                            }

                            $data = array('user_id' => $customer_id,
                            'stored_card_unique_id' => $arrayReponse['storeCardUniqueID'],
                            'masked_pan' => $arrayReponse['pan'],
                            'created_time' => now(),
                            'card_type' => $cardType,
                            'payment_scheme' => $arrayReponse['paymentScheme'],
                        );

                            /* Ken's code : dont need to foreach collection to filter $storeCardUniqueID */
                            $p2c2pTokenModel->getByCardUniqueToken($arrayReponse['storeCardUniqueID']);
                            if ($p2c2pTokenModel->getId()) {
                                $isFouned = true;
                            }

                            if (!$isFouned) {
                                $model = $p2c2pTokenModel->setData($data);
                                $model->save();
                            }
                        }

                        // create invoice for order

                        //set additional data for order
                        $order->setData('statuscode', $payment_status);
                        $order->setData('transaction_ref', $transaction_ref);
                        $order->setData('approval_code', $approval_code);
                        $order->save();

                        $invoice = $order->prepareInvoice()->register();
                        $payment = $order->getPayment();

                        $payment->setCreatedInvoice($invoice)
                        ->setIsTransactionClosed(false)
                        ->setIsTransactionPending(true)
                        ->addTransaction(
                            Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
                            $invoice,
                            false,
                            Mage::helper('p2c2p')->__('Capturing an amount of %s ', $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal()))
                        );

                        @$order->addRelatedObject($invoice);
                        $items = array();
                        foreach ($order->getAllItems() as $item) {
                            $items[$item->getId()] = $item->getQtyOrdered();
                        }
                        $invoiceId = Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(), $items, null, false, true);
                        #capture the invoice
                        Mage::getModel('sales/order_invoice_api')->capture($invoiceId);

                        return $this->_redirect('checkout/onepage/success');
                    } catch (Exception $e) {
                        Mage::getSingleton('core/session')->addError($e->getMessage());

                        return $this->_redirect('checkout/cart');
                    }
                }
                /*
                       APM Transaction Status
                       000 Success when paid with cash channel
                       001 Pending (Waiting customer to pay)
                       002 Rejected (Failed payment)
                       003 User cancel (Failed payment)
                       999 Error (Failed payment)
                    */
                else if($arrayReponse['respCode']=='001' || $arrayReponse['respCode']=='000') /// case when we using Payment channel
                {
                    if($arrayReponse['respCode']=='000') //case payment sucess
                    {
                        //set additional data for order
                        $order->setData('statuscode', $payment_status);
                        $order->setData('transaction_ref', $transaction_ref);
                        $order->setData('approval_code', $approval_code);
                        $order->save();

                        $invoice = $order->prepareInvoice()->register();
                        $payment = $order->getPayment();

                        $payment->setCreatedInvoice($invoice)
                            ->setIsTransactionClosed(false)
                            ->setIsTransactionPending(true)
                            ->addTransaction(
                                Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
                                $invoice,
                                false,
                                Mage::helper('p2c2p')->__('Capturing an amount of %s ', $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal()))
                            );

                        @$order->addRelatedObject($invoice);
                        $items = array();
                        foreach ($order->getAllItems() as $item) {
                            $items[$item->getId()] = $item->getQtyOrdered();
                        }
                        $invoiceId = Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(), $items, null, false, true);
                        #capture the invoice
                        Mage::getModel('sales/order_invoice_api')->capture($invoiceId);
                        $success = Mage::getStoreConfig('payment/p2c2p_onsite_internet_banking/toc2p_url_success', Mage::app()->getStore());
                        Mage::app()->getFrontController()->getResponse()->setRedirect($success)->sendResponse();
                        die;
                    }else
                    {
                        //set additional data for order
                        $order->setData('statuscode', $payment_status);
                        $order->setData('transaction_ref', $transaction_ref);
                        $order->setData('approval_code', $approval_code);
                        $order->save();
                        Mage::getSingleton('core/session')->addError('Please finish your payment');

                        $success = Mage::getStoreConfig('payment/p2c2p_onsite_internet_banking/toc2p_url_success', Mage::app()->getStore());
                        Mage::app()->getFrontController()->getResponse()->setRedirect($success)->sendResponse();
                        die;
                    }

                } elseif ($arrayReponse['respCode']=='05') {
                    Mage::getSingleton('core/session')->addError($this->__('Payment failed due to a posibility of insufficient funds or the transaction is declined.'));

                    return $this->_redirect('checkout/onepage/success');
                } elseif ($arrayReponse['respCode']=='51') {
                    Mage::getSingleton('core/session')->addError($this->__('Payment failed due to insufficient funds.'));

                    return $this->_redirect('checkout/onepage/success');
                } else {
                    Mage::getSingleton('core/session')->addError($this->__('Sorry, the credit card cannot be authorized for this transaction, please change your credit card or contact the issued bank.'));

                    return $this->_redirect('checkout/onepage/success');
                }
            } else {
                //If Signature does not match
                $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
                $order->setData('state', Mage_Sales_Model_Order::STATUS_FRAUD);
                $order->setData('statuscode', $payment_status);
                $order->setStatus(Mage_Sales_Model_Order::STATUS_FRAUD);
                $order->setData('transaction_ref', $transaction_ref);
                $order->setData('approval_code', $approval_code);
                $order->save();

                Mage::getSingleton('core/session')->addError('Your reponse signature do not match please contact for more information');

                return $this->_redirect('checkout/cart');
            }
        }
        catch(Exception $ex){
            Mage::log($ex->getMessage(), null, '2c2p-responseonsite.log', true);
        }
    }

    public function getHost()
    {
        $url = "https://t.2c2p.com/SecurePayment/PaymentAuth.aspx";
        $sandboxUrl = "https://demo2.2c2p.com/2C2PFrontEnd/SecurePayment/PaymentAuth.aspx";

        $test_mode = Mage::getStoreConfig('payment/p2c2p/gatewayurl', Mage::app()->getStore());

        if ($test_mode) {
            return $sandboxUrl;
        } else {
            return $url;
        }

    }

    public function responseAction()
    {
        $hashHelper = Mage::helper('p2c2p/hash');

        if (empty($_REQUEST) || empty($_REQUEST['order_id'])) {
            Mage_Core_Controller_Varien_Action::_redirect('');

            return;
        }

        $order = Mage::getModel('sales/order')->loadbyIncrementId($_REQUEST['order_id']);

        if (empty($order)) {
            Mage_Core_Controller_Varien_Action::_redirect('');

            return;
        }

        $payment_status_code = $_REQUEST['payment_status'];
        $transaction_ref = $_REQUEST['transaction_ref'];
        $approval_code = $_REQUEST['approval_code'];
        $payment_status = $_REQUEST['payment_status'];
        $orderId = $_REQUEST['order_id'];

        //Redirect to home page when hash value is wrong.
        if (!$hashHelper->isValidHash($_REQUEST)) {

            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            $order->setData('state', Mage_Sales_Model_Order::STATUS_FRAUD);
            $order->setData('statuscode', $payment_status);
            $order->setStatus(Mage_Sales_Model_Order::STATUS_FRAUD);
            $order->setData('transaction_ref', $transaction_ref);
            $order->setData('approval_code', $approval_code);
            foreach (Mage::getSingleton('checkout/session')->getQuote()->getItemsCollection() as $item) {
                Mage::getSingleton('checkout/cart')->removeItem($item->getId())->save();
            }

            Mage_Core_Controller_Varien_Action::_redirect('');

            return;
        }

        //Store response meta into p2c2p_meta database table.
        $metaHelper = Mage::helper('p2c2p/meta');
        $metaHelper->p2c2p_meta($_REQUEST);

        //Check payment status.
        if ($payment_status_code == "000" || $payment_status_code == "00") { // SUCCESS
            //Check if user is logged in or not.
            if (!empty($order->getCustomerId())) {
                if (!empty($_REQUEST['stored_card_unique_id'])) {
                    $customer_id = $order->getCustomerId();
                    $isFouned = false;

                    //Fatch data from database by customer ID.
                    $p2c2pTokenModel = Mage::getModel('p2c2p/token');

                    if (!$p2c2pTokenModel) {
                        die("2C2P Expected Model not available.");
                    }

                    $customer_data = $p2c2pTokenModel->getCollection()->addFieldToFilter('user_id', $customer_id);

                    $data = array('user_id' => $customer_id,
                        'stored_card_unique_id' => $_REQUEST['stored_card_unique_id'],
                        'masked_pan' => $_REQUEST['masked_pan'],
                        'created_time' => now());

                    //If matched the ignore if not match then add to database entry to prevent duplicate entry.
                    foreach ($customer_data as $key => $value) {
                        if (strcasecmp($value->getData('masked_pan'), $_REQUEST['masked_pan']) == 0 && strcasecmp($value->getData('stored_card_unique_id'), $_REQUEST['stored_card_unique_id']) == 0) {
                            $isFouned = true;
                            break;
                        }
                    }

                    if (!$isFouned) {
                        $model = $p2c2pTokenModel->setData($data);
                        $model->save();
                    }
                }
            }

            $state = Mage_Sales_Model_Order::STATE_PROCESSING;
            $status = Mage_Sales_Model_Order::STATE_PROCESSING;
            $order->setState($state, $status);

            $order->setData('statuscode', $payment_status);
            $order->setData('transaction_ref', $transaction_ref);
            $order->setData('approval_code', $approval_code);

            $order->save();

            $success = Mage::getStoreConfig('payment/p2c2p/toc2p_url_success', Mage::app()->getStore());
            Mage::app()->getFrontController()->getResponse()->setRedirect($success)->sendResponse();
            die;

            // $this->loadLayout();
            // $block = $this->getLayout()->createBlock('Mage_Core_Block_Template','p2c2p',array('template' => 'p2c2p/success.phtml'));
            // $this->getLayout()->getBlock('content')->append($block);
            // $this->renderLayout();
        } else {
            if ($payment_status_code == "001") { // Pending Payment

                $state = Mage_Sales_Model_Order::STATE_NEW;
                $status = "Pending_2C2P";

                $order->setState($state, $status);

                $order->setData('statuscode', $payment_status);
                $order->setData('transaction_ref', $transaction_ref);
                $order->setData('approval_code', $approval_code);

                $order->save();

                $success = Mage::getStoreConfig('payment/p2c2p/toc2p_url_success', Mage::app()->getStore());
                Mage::app()->getFrontController()->getResponse()->setRedirect($success)->sendResponse();
                die;

                // $this->loadLayout();
                //
                // $block = $this->getLayout()->createBlock('Mage_Core_Block_Template','p2c2p',array('template' => 'p2c2p/success.phtml'));
                // $this->getLayout()->getBlock('content')->append($block);
                // $this->renderLayout();
            } else {

                $order->setData('statuscode', $payment_status);
                $order->setData('transaction_ref', $transaction_ref);
                $order->setData('approval_code', $approval_code);

                if (Mage::getSingleton('checkout/session')->getLastRealOrderId()) {
                    if ($lastQuoteId = Mage::getSingleton('checkout/session')->getLastQuoteId()) {
                        $quote = Mage::getModel('sales/quote')->load($lastQuoteId);
                        $quote->setIsActive(true)->save();
                        $order->cancel()->save();
                    }
                    Mage::getSingleton('core/session')->addError($this->__('AN ERROR OCCURRED IN THE PROCESS OF PAYMENT'));
                    $this->_redirect('checkout/cart'); //Redirect to cart

                    return;
                }
            }
        }
    }
}