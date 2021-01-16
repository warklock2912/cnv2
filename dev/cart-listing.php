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
Mage::getSingleton('core/session', array('name' => 'frontend'));
$customer_id = Mage::getSingleton('customer/session')->getCustomerId();
if ($customer_id) {
    $customer = Mage::getModel('customer/customer')->load($customer_id);
    $quote = getQuote();
    $maxItems = (int) Mage::getStoreConfig('cartitems_options/Item/maxitems');

    if ($quote) {
        $productsResult = getCartDetails($quote, $customer_id);
        http_response_code(200);
        echo json_encode(array('status_code' => 200, 'message' => 'successfully', 'maxItems' => $maxItems, 'cartData' => $productsResult));
    } else {
        http_response_code(400);
        echo json_encode(array('status_code' => 400, 'message' => $e->getMessage()));
    }

} else {
    http_response_code(400);
    echo json_encode(array('status_code' => 400, 'message' => 'Invalid'));
}