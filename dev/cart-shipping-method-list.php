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

//Mage::getSingleton("core/session", array("name" => "frontend"));
if ($_REQUEST['id']) {
    $customerId = $_REQUEST['id'];

    $store_id = getStoreId();

    $customer = Mage::getModel('customer/customer')->load($customerId);
    $quote = Mage::getModel('sales/quote')->setSharedStoreIds($store_id)->loadByCustomer($customer);

    if ($quote) {

        $methods = Mage::getModel('checkout/cart_shipping_api')->getShippingMethodsList($quote->getId(), $store_id);
        $shipping_methods = array();

        $hashMethods = Mage::getModel('amtable/method')->getCollection()->toOptionHash();

        foreach ($methods as $method) {

            if ($method['code'] == 'storepickup_storepickup') {
                continue;
            }

            $shipping_methods[] = array(
                'name' => $method['method_title'],
                'shipping_id' => $method['code'],
                'method' => $method['method'],
                'price' => $method['price'],
                'duration' => $hashMethods[explode('amtable', $method['method'])[1]],
            );

        }

        http_response_code(200);
        echo json_encode(array('status_code' => 200, 'message' => 'successfully', 'shipping_methods' => $shipping_methods));
    } else {
        http_response_code(400);
        echo json_encode(array('status_code' => 400, 'message' => $e->getMessage()));
    }

} else {
    http_response_code(400);
    echo json_encode(array('status_code' => 400, 'message' => 'Invalid'));
}
