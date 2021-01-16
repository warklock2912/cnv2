<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);
$deviceToken = $data['deviceToken'];
$userID = $data['uuid'];
$platform = $data['platform'];
$deviceId = $data['deviceId'];
$collection = Mage::getModel('pushnotification/device')->getCollection()->addFieldToFilter('device_id', $deviceId);
$model = Mage::getModel('pushnotification/device');
if (count($collection)) {
	$model = $collection->getFirstItem()->setUserId($userID)->setDeviceToken($deviceToken)->setPlatform($platform);
    $collectionByUser = Mage::getModel('pushnotification/device')
        ->getCollection()
        ->addFieldToFilter('user_id', $userID);
    if (count($collectionByUser)) {
        $modelByUser = $collectionByUser->getFirstItem()->setUserId('');
        $modelByUser->save();
    }
} else {
	$collectionByUser = Mage::getModel('pushnotification/device')
		->getCollection()
		->addFieldToFilter('user_id', $userID);

	if (count($collectionByUser)) {
		$modelByUser = $collectionByUser->getFirstItem()->setUserId('');
		$modelByUser->save();
	}

	$model->setUserId($userID)->setDeviceToken($deviceToken)->setPlatform($platform)->setDeviceId($deviceId);
}
try {
	$model->save();
	dataResponse(200, 'Add Successfully');
} catch (Exception $e) {
	dataResponse(400, $e->getMessage());
}

