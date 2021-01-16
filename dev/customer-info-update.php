<?php

require_once '../app/Mage.php';
require_once 'functions.php';
checkIsLoggedIn();


//Mage::getSingleton("core/session", array("name" => "frontend"));

$websiteId = Mage::app()->getWebsite()->getId();
$store = Mage::app()->getStore();

$_helper = Mage::helper('netgo_customerpic');
$root_path = $_helper->getBaseDir();

$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);

$first_name = isset($data['first_name']) ? $data['first_name'] : null;
$last_name = isset($data['last_name']) ? $data['last_name'] : null;
$email = isset($data['email']) ? $data['email'] : null;
$telephone = isset($data['telephone']) ? $data['telephone'] : null;
$gender = isset($data['gender']) ? $data['gender'] : null;
$dob = isset($data['birth_day']) ? $data['birth_day'] : null;
$vip_member_id = isset($data['vip_member']) ? $data['vip_member'] : null;
$customerId = $data['user_id'];
$profilePhoto = isset($data['profile_photo']) ? substr($data['profile_photo'], 0, -1) : null;
$profilePhotoName = md5($email . date('m/d/Y h:i:s')) . ".png";
$profilePhotoPath = $root_path . "/media/profile/";

if ($email != null) {
	$customer = Mage::getModel("customer/customer")->load($customerId);
	$customer->setWebsiteId($websiteId)
		->setStore($store)
		->setFirstname($first_name)
		->setLastname($last_name)
		->setEmail($email)
		->setTelephone($telephone)
		->setGender($gender)
		->setVipMemberId($vip_member_id)
		->setDob($dob);
	if ($profilePhoto != null) {
		if (!is_dir($profilePhotoPath)) {
			mkdir($profilePhotoPath, 0777, TRUE);
		}
		file_put_contents($profilePhotoPath . $profilePhotoName, base64_decode($profilePhoto));
		$customer->setProfilePhoto("media/profile/" . $profilePhotoName);
	}
	try {
		$customer->save();
		$userData = getCustomerData($customer);

		http_response_code(200);
		echo json_encode(array('status_code' => 200, 'message' => 'Update info Successful', 'accountInfomation' => $userData));
	} catch (Exception $e) {
		//Zend_Debug::dump($e->getMessage());
		http_response_code(400);
		echo json_encode(array('status_code' => 400, 'message' => $e->getMessage()));
	}
} else {
	http_response_code(400);
	echo json_encode(array('status_code' => 400, 'message' => 'Not enough Infomation'));
}