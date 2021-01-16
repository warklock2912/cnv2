<?php
/**
 * Created by PhpStorm.
 * User: bach95
 * Date: 31/08/2018
 * Time: 10:03
 */
require_once '../app/Mage.php';
require_once 'functions.php';
require_once '../lib/nusoap/nusoap.php';
checkIsLoggedIn();

//Mage::getSingleton("core/session", array("name" => "frontend"));

if ($_REQUEST['id']) {
	$customerId = $_REQUEST['id'];
	$customer = Mage::getModel('customer/customer')->load($customerId);
	$data = array();
	if (count($customer)) {
		$data = getCustomerData($customer);

		Mage::dispatchEvent('customer_update_app', array( 'customer' => $customer) );

		http_response_code(200);
		echo json_encode(array('status_code' => 200, 'message' => 'valid', 'accountInfomation' => $data));
	} else {
		http_response_code(200);
		echo json_encode(array('status_code' => 200, 'message' => 'valid', 'accountInfomation' => $data));
	}
} else {
	http_response_code(400);
	echo json_encode(array('status_code' => 400, 'message' => 'Invalid Customer'));
}
