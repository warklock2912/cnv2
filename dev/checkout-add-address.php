<?php
require_once '../app/Mage.php';
require_once 'functions.php';
checkIsLoggedIn();

//Mage::getSingleton("core/session", array("name" => "frontend"));

$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);

if ($data['shipping_address_id']) {


    $shippingAddressId = $data['shipping_address_id'];
    $billingAddressId = $data['billing_address_id'];
	$quote = getQuote();
    $shippingAddress = Mage::getModel('customer/address')->load($shippingAddressId);
    $billingAddress = Mage::getModel('customer/address')->load($billingAddressId);
    try {
        $quote->setBillingAddress($billingAddress);
        $quote->setShippingAddress($shippingAddress);
	    $quote->getShippingAddress()->implodeStreetAddress();
	    $quote->getShippingAddress()->setCollectShippingRates(true);
	    $quote->collectTotals()->save();

	    $store_id = getStoreId();
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
        dataResponse(200, 'valid', $shipping_methods);

    } catch (Exception $e) {
        dataResponse(400, 'Invalid', $e->getMessage());
    }
} else {
    dataResponse(400, 'Invalid');

}

