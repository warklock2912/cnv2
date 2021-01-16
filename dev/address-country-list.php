<?php
require_once '../app/Mage.php';
require_once 'functions.php';
$countryList = Mage::getModel('directory/country')->getResourceCollection()
	->loadByStore()
	->toOptionArray(true);
$data = array();
$dataArr = array();
if (count($countryList)) {
	foreach ($countryList as $country):;
		$data['code'] = $country['value'];
		$data['label'] = (string)Mage::app()->getLocale()->getCountryTranslation($country['value']);
		$dataArr[] = $data;
	endforeach;
	http_response_code(200);
	echo json_encode(array('status_code' => 200, 'message' => 'valid', 'countryArr' => $dataArr));
} else {
	http_response_code(200);
	echo json_encode(array('status_code' => 200, 'message' => 'No Data', 'countryArr' => $dataArr));
}
