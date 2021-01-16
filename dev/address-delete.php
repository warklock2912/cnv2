<?php
require_once '../app/Mage.php';
require_once 'functions.php';
checkIsLoggedIn();
//Mage::getSingleton("core/session", array("name" => "frontend"));

$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);

if ($data['id']) {
	$addressId = $data['id'];
	$customAddress = Mage::getModel('customer/address');
	$customAddress->load($addressId);
	try{
		$customAddress->delete();
		$result = array();
		$result['addressId'] = $addressId;
		http_response_code(200);
		echo json_encode(array('status_code' => 200, 'message' => 'valid', 'succeed' => $addressId));
	}
	catch(Exception $e){
		http_response_code(400);
		echo json_encode(array('status_code' => 400, 'message' => 'invalid', 'succeed' => $e->getMessage()));
	}
} else {
	http_response_code(400);
	echo json_encode(array('status_code' => 400, 'message' => 'invalid Post'));
}

