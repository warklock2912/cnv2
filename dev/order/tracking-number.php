<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();
if ($_REQUEST['id']) {
	$data = '';
	$order_id = $_REQUEST['id'];
	$order = Mage::getModel('sales/order')
		->load($order_id);
	foreach ($order->getShipmentsCollection() as $shipment){
		$tracks = $shipment->getAllTracks();
		$data = $tracks[0]->getTrackNumber();
	}
	dataResponse(200, 'valid', $data);
} else
	dataResponse(400, 'Missing id param');