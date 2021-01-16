<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();
$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);
if ($data['ids']) {
	$notificationIdsArr = $data['ids'];
	foreach ($notificationIdsArr as $notificationId) {
		$notification = Mage::getModel('pushnotification/notificationlist')->load($notificationId);
		$notification->delete();
	}
	dataResponse(200, 'valid');
} else {
	dataResponse(400, 'Invalid');
}