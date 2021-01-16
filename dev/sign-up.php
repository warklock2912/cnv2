<?php
/**
 * Created by PhpStorm.
 * User: tuan
 * Date: 8/24/18
 * Time: 11:13 AM
 */

require_once '../app/Mage.php';
require_once 'functions.php';
require_once '../lib/nusoap/nusoap.php';

Mage::getSingleton("core/session", array("name" => "frontend"));

$websiteId = Mage::app()->getWebsite()->getId();
$store = Mage::app()->getStore();

$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);

$first_name = isset($data['first_name']) ? $data['first_name'] : null;
$last_name = isset($data['last_name']) ? $data['last_name'] : null;
$email = isset($data['email']) ? $data['email'] : null;
$password = isset($data['password']) ? $data['password'] : null;
$memberId = isset($data['vip_member_id']) ? $data['vip_member_id'] : null;


if ($email != null && $password != null) {
	$customer = Mage::getModel("customer/customer");
	$customer->setWebsiteId($websiteId)
		->setStore($store)
		->setFirstname($first_name)
		->setLastname($last_name)
		->setEmail($email)
		->setPassword($password);
	if (!empty($memberId)){
        $customer->setData('vip_member_id',$memberId);
        $customer->setData('vip_member_status','1');
    }
	try {
		$customer->save();
		// Mage::log('dispatchEvent:customer_register_success', null, 'api-sign-up.log', true);
        Mage::dispatchEvent('customer_register_success',
            array( 'customer' => $customer)
        );
		// get customer info to Response
		$userData = getCustomerData($customer);
		$session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
		$session->login($email, $password);
		$quoteObj = Mage::getModel('sales/quote');
		$quoteObj->assignCustomer($customer);
		$quoteObj->setStoreId(getStoreId());
		$quoteObj->collectTotals();
		$quoteObj->setIsActive(true);
		$quoteObj->save();
        $customer->sendNewAccountEmail(
            'registered',
            '',
            getStoreId()
        );

        // add mobile token;
        addToken($customer);
		http_response_code(200);
		echo json_encode(array('status_code' => 200, 'message' => 'Create Account Successful', 'accountInfomation' => $userData));
	} catch (Exception $e) {
		//Zend_Debug::dump($e->getMessage());
		http_response_code(400);
		echo json_encode(array('status_code' => 400, 'message' => $e->getMessage()));
	}
}
