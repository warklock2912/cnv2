<?php
require_once '../app/Mage.php';
require_once 'functions.php';
checkIsLoggedIn();
//Mage::getSingleton("core/session", array("name" => "frontend"));

if ($_REQUEST['id']) {
	$customerId = $_REQUEST['id'];
	$customer = Mage::getModel('customer/customer')->load($customerId);
	$defaultBillingId = $customer->getDefaultBilling();
	$defaultShippingId = $customer->getDefaultShipping();
	$allAddress = $customer->getAddresses();
	$data = array();
	if (count($allAddress)) {
		foreach ($allAddress as $address) {
			$city = Mage::getModel('customaddress/city')->load($address->getCityId());
			$region = Mage::getModel('directory/region')->load($address->getRegionId());
			$subdistrict = Mage::getModel('customaddress/subdistrict')->load($address->getSubdistrictId());

			$addressObj = array(
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
			);
			if ($defaultBillingId == $address->getId())
				$addressObj['is_default_billing'] = 1;
			else
				$addressObj['is_default_billing'] = 0;
			if ($defaultShippingId == $address->getId())
				$addressObj['is_default_shipping'] = 1;
			else
				$addressObj['is_default_shipping'] = 0;
			$data[] = $addressObj;
		}
		http_response_code(200);
		echo json_encode(array('status_code' => 200, 'message' => 'valid', 'addressData' => $data));
	} else {
		http_response_code(200);
		echo json_encode(array('status_code' => 200, 'message' => 'valid', 'addressData' => $data));
	}
} else {
	http_response_code(400);
	echo json_encode(array('status_code' => 400, 'message' => 'Invalid Customer'));
}
