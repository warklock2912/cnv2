<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
$collection = Mage::getModel('pushnotification/device')->getCollection();
$dataArr = array();
foreach ($collection as $device):;
	$data['id'] = $device->getId();
	$data['user_id'] = $device->getUserId();
	$data['device_id'] = $device->getDeviceId();
	$data['token'] = $device->getDeviceToken();
	$data['platform'] = $device->getPlatForm();
	$dataArr[] = $data;
endforeach;
dataResponse(200, 'valid', $dataArr);