<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
$notificationCollection = Mage::getModel('pushnotification/notificationlist')->getCollection();

foreach ($notificationCollection as $notification) {
		$notification->delete();
	}
	dataResponse(200, 'valid');
