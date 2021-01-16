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

    /*$clientToken = $gateway->clientToken()->generate([
        "customerId" => $customer_id
    ]);*/

    $clientToken = $gateway->clientToken()->generate();

    http_response_code(200);
    echo json_encode(array('status_code' => 200, 'message' => 'valid', 'client_token' => $clientToken));
} catch (Exception $e) {
    $message = $e->getMessage() ? $e->getMessage() : 'Wrong key!';
    http_response_code(400);
    echo json_encode(array('status_code' => 400, 'message' => $message));
    Mage::logException($e);
}
