<?php
/**
 * Created by PhpStorm.
 * User: bach95
 * Date: 12/09/2018
 * Time: 10:19
 */
require_once '../app/Mage.php';
require_once 'functions.php';
checkIsLoggedIn();

//	$customer = Mage::getModel('customer/customer')->load($customerId);


$quote = getQuote();
$store_id = getStoreId();

$omise = Mage::getSingleton('omise_gateway/config')->load(1);
if ($omise->getTestMode()) {
    $public_key = $omise->getPublicKeyTest();
    $secret_key = $omise->getSecretKeyTest();
} else {
    $public_key = $omise->getPublicKey();
    $secret_key = $omise->getSecretKey();
}

if ($quote) {
    $payments = Mage::helper('ampayrestriction/payment_data')->getStoreMethods($store_id, $quote);
    $payment_methods = array();

    foreach ($payments as $paymentModel) {
        $paymentCode = $paymentModel->getCode();

        if (strpos($paymentCode, 'paypal_express') !== false || strpos($paymentCode, 'braintree') !== false) {
            continue;
        }

        $paymentTitle = Mage::getStoreConfig('payment/' . $paymentCode . '/title', $store_id);
        /*$payment_methods[$paymentCode] = array(
            'label'   => $paymentTitle,
            'value' => $paymentCode,
        );*/
        if ($paymentCode == 'omise_gateway') {
            $payment_methods[] = array(
                'name' => $paymentTitle,
                'value' => $paymentCode,
                'public_key' => $public_key,
                'secret_key' => $secret_key,
            );
        } else if ($paymentCode == 'crystal_paypal') {
            $payment_methods[] = array(
                'name' => $paymentTitle,
                'value' => $paymentCode,
                'live_key' =>  Mage::getStoreConfig("payment/crystal_paypal/client_key_live"),
                'test_key' =>  Mage::getStoreConfig("payment/crystal_paypal/client_key_test"),
            );
        } else if ($paymentCode == 'p2c2p_onsite_internet_banking') {
            $payment_methods[] = array(
                'name' => $paymentTitle,
                'value' => $paymentCode,
                'image' => Mage::getStoreConfig("payment/p2c2p_onsite_internet_banking/toc2p_mobile_image"),
            );
        } else if ($paymentCode == 'kpayment_credit') {
            $kHelper  = Mage::helper('kpayment');
            $cpBlock = Mage::app()->getLayout()->getBlockSingleton('Tigren_Kpayment_Block_Credit_Credit');
            list($publicKey, $inlineJavascriptUrl, $currency, $amount) = $cpBlock->getKbankCreditInformation();
            $payment_methods[] = array(
                'name' => $paymentTitle,
                'value' => $paymentCode,
                'public_key' => $publicKey,
                'api_url' => $kHelper->getConfigData('kpayment_credit','api_base_url').'/token',
                'kinline' => $inlineJavascriptUrl,
                'card_list' => array(),
//                'cust_id' => $customerKbankApiId,
            );
        } else {
            $payment_methods[] = array(
                'name' => $paymentTitle,
                'value' => $paymentCode,
            );
        }

    }
    foreach ($payment_methods as $index => $method){
        if($method['value'] == "omise_gateway"){
            $temp = $payment_methods[0];
            $payment_methods[0] = $payment_methods[$index];
            $payment_methods[$index] = $temp;
        }
        if($method['value'] == "crystal_paypal"){
            $temp = $payment_methods[1];
            $payment_methods[1] = $payment_methods[$index];
            $payment_methods[$index] = $temp;
        }
    }

    http_response_code(200);
    echo json_encode(array('status_code' => 200, 'message' => 'successfully', 'payment_methods' => $payment_methods));
} else {
    http_response_code(400);
    echo json_encode(array('status_code' => 400, 'message' => 'Invalid'));
}

