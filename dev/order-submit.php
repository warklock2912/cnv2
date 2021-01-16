<?php
require_once '../app/Mage.php';
require_once 'functions.php';
require_once(Mage::getBaseDir('lib') . '/Crystal/Braintree/lib/Braintree.php');
require_once(Mage::getBaseDir('lib') . '/omise-php/lib/Omise.php');
checkIsLoggedIn();

function dateThai($format = 'Y-m-d H:i:s'){
    return date($format, strtotime('+7 hour', strtotime(gmdate('Y-m-d H:i:s'))));
}

$omise = Mage::getSingleton('omise_gateway/config')->load(1);
define('OMISE_API_VERSION', '2014-07-27');
if ($omise->getTestMode()) {
    define('OMISE_PUBLIC_KEY', $omise->getPublicKeyTest());
    define('OMISE_SECRET_KEY', $omise->getSecretKeyTest());
} else {
    define('OMISE_PUBLIC_KEY', $omise->getPublicKey());
    define('OMISE_SECRET_KEY', $omise->getSecretKey());
}

$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);

$quote_id = $data['quote_id'];
$payment = $data['payment'];
$payment_method = $data['payment']['method'];
$shipping_method = $data['shipping_method'];
$customer_id = $data['customer_id'];
$shipping_address_id = $data['shipping_address_id'];
$billing_address_id = $data['billing_address_id'];
$store_id = getStoreId();
try {
    $quote = getQuote();
    $customer = Mage::getModel('customer/customer')->load($customer_id);
    $shipping_address = Mage::getModel('customer/address')->load($shipping_address_id);
    $billing_address = Mage::getModel('customer/address')->load($billing_address_id);

    // fix add shipping and billding address
    $quote->getBillingAddress()->addData($billing_address->getData());
    $shippingAddress = $quote->getShippingAddress()->addData($shipping_address->getData());

    $shippingAddress->setCollectShippingRates()
        ->collectShippingRates()
        ->setShippingMethod($shipping_method)
        ->setPaymentMethod($payment_method);

    $quote->getPayment()->importData($payment);
    $quote->setStoreId(getStoreId());

    $quote->collectTotals()->save();
    $service = Mage::getModel('sales/service_quote', $quote);

    if( $payment_method == 'crystal_twoctwop'){
        Mage::log('==== before submit => customer id = ' . $customer_id . ' && quote id = ' . $quote_id,null,'order_submit_'.dateThai('Y-m-d').'.log',true);
        $items = Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems();
        $itemsObj = [];
        $k = 0;
        foreach($items as $item) {
            $itemsObj[$k]['id'] =  $item->getProductId();
            $itemsObj[$k]['sku'] =  $item->getSku();
            $itemsObj[$k]['qty'] =  $item->getQty();
            $k++;
        }

        Mage::log(json_encode($itemsObj),null,'order_submit_'.dateThai('Y-m-d').'.log',true);
    }

    $service->submitAll();

    $order = $service->getOrder();
    switch ($payment_method) {
        case 'crystal_paypal':
            $details = array(
                'Payment Method' => 'Paypal on app',
                'create_time' => gmdate("YmdHis", time()),
            );
            break;
        case 'p2c2p_onsite_internet_banking':
            $details = array(
                'Payment Method' => 'Onsite internet banking on app',
                'create_time' => gmdate("YmdHis", time()),
            );
            break;
        case 'crystal_twoctwop':
            $quote->removeAllItems()->save();

            $paymentDetail = new stdClass();

            $desc = $order->getData('customer_email');
            $invoice_no = $order->getRealOrderId();
            $currency_code = "THB";
            $amount = (string )$order->getGrandTotal();
            $amount = Mage::helper('twoctwop')->formatAmount($amount);
            $cards = Mage::getModel('p2c2p/token')->getCollection()->addFieldToFilter('user_id', $customer->getId());
            if (!count($cards)) {
                $paymentDetail->userDefined5 = "true";
            }
            // $request_3ds = CardSecureMode::YES;

            //Construct payment token request
            $paymentDetail->invoiceNo = $invoice_no;
            $paymentDetail->desc = $desc;
            $paymentDetail->amount = $amount;
            $paymentDetail->currencyCode = $currency_code;
            // $paymentDetail->request3DS = $request_3ds;
//            $paymentDetail->nonceStr = $nonce_str;
            $paymentDetail->userDefined1 = $customer->getId();
            $paymentDetail->userDefined2 = "payment";
            //Important: Verify response signature

            $paymentTokenResult = Mage::helper('twoctwop')->getPaymentToken($paymentDetail);
            $paymentTokenObj = $paymentTokenResult['payment_response'];
            if ($paymentTokenResult['status'] ) {
                dataResponse(200, $paymentTokenResult['message'], $paymentTokenObj);
            } else {
                dataResponse(400, $paymentTokenResult['message']);
            };
            die;
            break;
        case 'kpayment_credit':
            // Log order created message
            $kpaymentHelper = Mage::helper('kpayment');
            $incrementId = $order->getRealOrderId();
            Mage::log('Mobileapp - Order created with increment id: ' . $incrementId, null, 'kpayment_credit.log');

            $currency = Mage::app()->getStore()->getBaseCurrencyCode();
            $amount = $order->getGrandTotal();

            $additionalData = array(
                'mid' => $kpaymentHelper->getMerchantIdCredit(),
                'tid' => $kpaymentHelper->getTerminalIdCredit()
            );
            $params = array(
                'token' => $data['payment']['token'],
                'amount' => $amount,
                'currency' => $currency,
                'description' => 'Charge order: ' . $incrementId,
                'source_type' => 'card',
                'mode' => 'token',
                'reference_order' => $incrementId,
                'additional_data' => $additionalData,
                'savecard' => $data['payment']['saveCard'] ? 'true' : 'false'
            );

            if ($data['payment']['card_id']) {
                $params = array(
                    'amount' => $amount,
                    'currency' => $currency,
                    'description' => 'Charge order: ' . $incrementId,
                    'source_type' => 'card',
                    'mode' => 'customer',
                    'reference_order' => $incrementId,
                    'additional_data' => $additionalData,
                    'customer' => array(
                        'customer_id' => $data['payment']['cust_id'],
                        'card_id' => $data['payment']['card_id']
                    )
                );
            }

            /** @var Tigren_Kpayment_Model_Charge $charge **/
            $charge = Mage::getModel('kpayment/charge');

            $isPrivate = $kpaymentHelper->getIsPrivateCredit();
            $kpaymentHelper->logAPI('[Mobileapp - CHARGE REQUEST Kpayment KpaymentCode]', 'credit');
            $kpaymentHelper->logAPI(array($params), 'credit');
            $response = getPaymentResult($params, $isPrivate);

            $kpaymentHelper->logAPI('[Mobileapp - CHARGE $response Kpayment KpaymentCode]', 'credit');
            $kpaymentHelper->logAPI($response, 'credit');
            $createCharge = json_decode($response, true);

            if(!empty($createCharge['id']) && $createCharge['status'] == 'success'){
                $kpaymentHelper->logAPI($createCharge, 'credit');

                $payment = $order->getPayment();

                saveReferencePayment($payment, $order, $createCharge, $data['token']);

                $kpaymentHelper->logAPI('[Mobileapp - CHARGE RESPONSE Kpayment KpaymentCode]', 'credit');

                if (!empty($createCharge['redirect_url']) && $createCharge['transaction_state'] != 'Authorized'){
                    $quote->removeAllItems()->save();
                    $resData = $order->getData();
                    $quote->removeAllItems()->save();
                    $resData['redirect_url'] =  $createCharge['redirect_url'];
                    dataResponse(200, 'successfully', $resData);
                    die;
                }
                else {
                    $headerPrivate = array(
                        'Content-Type: ' . 'application/json; charset=UTF-8',
                        'x-api-key: ' . $kpaymentHelper->getSecretKey()
                    );

                    $objectId = $createCharge['id'];

                    $store = $order->getStore();

                    if (!$order || !$order->getId()) {
                        throw new Exception('Payment failure!');
                    }

                    $payment = $order->getPayment();

                    $paymentMethod = $payment->getMethod();
                    if ($paymentMethod == 'kpayment_credit') {

                        $url = $kpaymentHelper->getConfigData('kpayment_credit','api_base_url'). '/charge/' . $objectId;
                        $kpaymentHelper->logAPI('[CALLBACK REQUEST Kpayment KpaymentCode]', 'credit');
                        $kpaymentHelper->logAPI($url, 'credit');

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

                        $kpaymentHelper->logAPI('[CALLBACK RESPONSE Kpayment KpaymentCode]', 'credit');
                        $kpaymentHelper->logAPI(array($response), 'credit');
                        $kpaymentHelper->logAPI('===== END =====', 'credit');

                        if (!empty($response['status']) && $response['status'] === 'success') {
                            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
                            $order->addStatusToHistory($kpaymentHelper->getOrderStatusPaidCredit(), $responseMessage);
                            $order->save();
                            if ($kpaymentHelper->getCreateAutoInvoiceCredit()) {
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

                            updateInquiryStatus($createCharge['id'], $order->getIncrementId(), $createCharge['status']);
                        }
                        elseif(!empty($response['status']) && $response['status'] === 'pending') {
                            $history = $order->addStatusHistoryComment($responseMessage);
                            $history->setIsVisibleOnFront(false);
                            $history->setIsCustomerNotified(false);
                            $history->save();

                            throw new Exception('Sorry, the credit card cannot be authorized for this transaction, please change your credit card or contact the issued bank.');
                        }
                        else {
                            $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
                            $order->setState($state)
                                ->addStatusToHistory(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, $responseMessage)
                                ->save();
                            throw new Exception('Sorry, the credit card cannot be authorized for this transaction, please change your credit card or contact the issued bank.');
                        }
                    }
                }
            }
            else {
                $quote->removeAllItems()->save();
                dataResponse(200, 'Payment Failed!');
                die;
            }
            break;
        default:
            if ($order->getCanSendNewEmailFlag()) {
                $order->queueNewOrderEmail();
            }
    }
    //$increment_id = $service->getOrder()->getRealOrderId();
    $payment = $order->getPayment();

    $payment
        ->setAdditionalData(serialize($details))
        ->save();
    $data = $order->getData();
    $quote->removeAllItems()->save();
    dataResponse(200, 'successfully', $data);
    Mage::log('**** order create ****',null,'order_submit_'.dateThai('Y-m-d').'.log',true);
} catch (Exception $e) {
    if (strlen($e->getMessage()) <= 100) {
        dataResponse(400, $e->getMessage());
    } else {
        dataResponse(400, "Error when create new order.");
    }
}
