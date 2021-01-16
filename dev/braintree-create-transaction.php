<?php
/**
 * Created by PhpStorm.
 * User: tuan
 * Date: 9/7/18
 * Time: 1:53 PM
 */

require_once '../app/Mage.php';
require_once 'functions.php';
require_once(Mage::getBaseDir('lib') . '/Crystal/Braintree/lib/Braintree.php');

$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);

$customer_id = $data['customer_id'];
$grand_total = $data['grand_total'];
$payment_method_nonce = $data['payment_method_nonce'];
//$payment_method_nonce = 'tokencc_bj_tktz5w_yc2qyv_pxfx8j_grdzp8_xz5';

$environment = Mage::getStoreConfig("payment/crystal_braintree/sandbox");
$merchant_id = Mage::getStoreConfig("payment/crystal_braintree/merchant_id");
$public_key = Mage::getStoreConfig("payment/crystal_braintree/public_key");
$secret_key = Mage::getStoreConfig("payment/crystal_braintree/secret_key");

try{

    $gateway = new Braintree_Gateway([
        'environment' => 'sandbox',
        'merchantId' => $merchant_id,
        'publicKey' => $public_key,
        'privateKey' => $secret_key
    ]);

    $result = $gateway->transaction()->sale([
        'amount' => $grand_total,
        'paymentMethodNonce' => $payment_method_nonce,
        'options' => [
            'submitForSettlement' => True
        ]
    ]);

    if ($result->success) {
        http_response_code(200);
        echo json_encode(array('status_code' => 200, 'message' => 'success'));
    } else {
        http_response_code(400);
        echo json_encode(array('status_code' => 400, 'message' => 'transaction error'));
    }


} catch (Exception $e) {
    $message = $e->getMessage() ? $e->getMessage() : 'Wrong key!';
    http_response_code(400);
    echo json_encode(array('status_code' => 400, 'message' => $message));
    Mage::logException($e);
}