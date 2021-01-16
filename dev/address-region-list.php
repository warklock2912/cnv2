<?php
require_once '../app/Mage.php';
require_once 'functions.php';
checkIsLoggedIn();
$regionList = Mage::getModel('directory/region')->getResourceCollection()
	->setOrder('region_id', Varien_Data_Collection::SORT_ORDER_ASC)
	->load();
$data = array();
$dataArr = array();
if (count($regionList)) {
	foreach ($regionList as $region):;
		$data['country_id'] = $region->getCountryId();
		$data['region_id'] = $region->getRegionId();
		$data['code'] = $region->getCode();
		$data['name'] = $region->getName();
		$dataArr[] = $data;
	endforeach;
	http_response_code(200);
	echo json_encode(array('status_code' => 200, 'message' => 'valid', 'regionArr' => $dataArr));
} else {
	http_response_code(200);
	echo json_encode(array('status_code' => 200, 'message' => 'No Data', 'regionArr' => $dataArr));
}
