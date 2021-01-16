<?php
require_once '../app/Mage.php';
require_once 'functions.php';


$subdistricts = Mage::getModel('customaddress/subdistrict')->getCollection();
$data = array();
$dataArr = array();
if (count($subdistricts)) {
	foreach ($subdistricts as $subdistrict):;
		$data['city_id'] = $subdistrict->getCityId();
		$data['subdistrict_id'] = $subdistrict->getSubdistrictId();
		$data['code'] = $subdistrict->getCode();
		$data['name'] = $subdistrict->getName();
		$data['zipcode'] = $subdistrict->getZipcode();
		$dataArr[] = $data;
	endforeach;
	http_response_code(200);
	echo json_encode(array('status_code' => 200, 'message' => 'valid', 'subdistrictArr' => $dataArr));
} else {
	http_response_code(200);
	echo json_encode(array('status_code' => 200, 'message' => 'No Data', 'subdistrictArr' => $dataArr));
}
