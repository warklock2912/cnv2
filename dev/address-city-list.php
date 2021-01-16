<?php
require_once '../app/Mage.php';
require_once 'functions.php';

$regionId = $_REQUEST['region_id'];
$cities = Mage::getModel('customaddress/city')->getCollection();
$data = array();
$dataArr = array();
if (count($cities)) {
	foreach ($cities as $city):;
		$data['region_id'] = $city->getRegionId();
		$data['city_id'] = $city->getCityId();
		$data['code'] = $city->getCode();
		$data['name'] = $city->getName();
		$dataArr[] = $data;
	endforeach;
	http_response_code(200);
	echo json_encode(array('status_code' => 200, 'message' => 'valid', 'cityArr' => $dataArr));
} else {
	http_response_code(200);
	echo json_encode(array('status_code' => 200, 'message' => 'No Data', 'cityArr' => $dataArr));
}
