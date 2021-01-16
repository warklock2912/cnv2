<?php
require_once '../app/Mage.php';
require_once 'functions.php';
checkIsLoggedIn();
$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);
$address = Mage::getModel('customer/address');
if($data['user_id']){
	if ($data['id']) {
		$addressId = $data['id'];
		$address->load($addressId);
	}
	$dataUpdate = array(
		'telephone' => $data['telephone'] ? $data['telephone'] : null,
		'street' => $data['street'] ? $data['street'] : null,
		'first_name' => $data['first_name'] ? $data['first_name'] : null,
		'country_id' => $data['country_id'] ? $data['country_id'] : 'TH',
		'last_name' => $data['last_name'] ? $data['last_name'] : null,
		'city_id' => $data['city_id'] ? $data['city_id'] : null,
		'region_id' => $data['region_id'] ? $data['region_id'] : null,
		'subdistrict_id' => $data['subdistrict_id'] ? $data['subdistrict_id'] : null,
		'post_code' => $data['post_code'] ? $data['post_code'] : null,
		'is_default_billing' => $data['is_default_billing'] ? $data['is_default_billing'] : 0,
		'is_default_shipping' => $data['is_default_shipping'] ? $data['is_default_shipping'] : 0,
		'user_id' =>  $data['user_id'],
	);
	try {

		$city = Mage::getModel('customaddress/city')->load($dataUpdate['city_id']);
		$region = Mage::getModel('directory/region')->load($dataUpdate['region_id']);
		$subdistrict = Mage::getModel('customaddress/subdistrict')->load($dataUpdate['subdistrict_id']);
		$address->setCustomerId($dataUpdate['user_id'])
			->setCountryId($dataUpdate['country_id'])
			->setFirstname($dataUpdate['first_name'])
			->setLastname($dataUpdate['last_name'])
			->setTelephone($dataUpdate['telephone'])
			->setData('street', $dataUpdate['street'])
			->setRegionId($dataUpdate['region_id'])
			->setRegion($region->getName())
			->setCityId($dataUpdate['city_id'])
			->setCity($city->getName())
			->setSubdistrictId($dataUpdate['subdistrict_id'])
			->setSubdistrict($subdistrict->getName())
			->setPostcode($dataUpdate['post_code'])
			->setIsDefaultBilling($dataUpdate['is_default_billing'])
			->setIsDefaultShipping($dataUpdate['is_default_shipping']);
		$address->save();

		$addressUpdated = array(
			'id' => $address->getId(),
			'first_name' => $address->getFirstname(),
			'last_name' => $address->getLastname(),
			'telephone' => $address->getTelephone(),
			'street' => $address->getData('street'),
			'city' => array(
				'city_id' => $city->getId(),
				'code' => $city->getCode(),
				'name' => $city->getName(),
			),
			'region' => array(
				'region_id' => $region->getId(),
				'code' => $region->getCode(),
				'name' => $region->getName(),
			),
			'subdistrict' => array(
				'subdistrict_id' => $subdistrict->getId(),
				'code' => $subdistrict->getCode(),
				'name' => $subdistrict->getName(),
			),
			'country' => array(
				'code' => $address->getCountryId(),
				'label' => Mage::app()->getLocale()->getCountryTranslation($address->getCountry()),
			),
			'post_code' => $address->getPostcode(),
			'is_default_billing' => $address->getIsDefaultBilling(),
			'is_default_shipping' => $address->getIsDefaultShipping()
		);
		http_response_code(200);
		echo json_encode(array('status_code' => 200, 'message' => 'successfull', 'addressData' => $addressUpdated));
	} catch (Exception $e) {
		http_response_code(400);
		echo json_encode(array('status_code' => 400, 'message' => $e->getMessage()));
	}
} else {
	http_response_code(400);
	echo json_encode(array('status_code' => 400, 'message' => 'Invalid Post'));
}




