<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();
$customer = Mage::getSingleton('customer/session')->getCustomer();
$customerId = $customer->getId();
$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);
if ($data['activity_id']):
	$campaignId = $data['activity_id'];
	$queueCurrent = Mage::getModel('campaignmanage/queue')->getCollection()
		->addFieldToFilter('campaign_id', $campaignId)
		->addFieldToFilter('customer_id', $customerId)
		->getFirstItem();

	try {
		$queueCurrent->delete();
        updateNoQueue($campaignId, $customerId);
		dataResponse(200, 'Successfully Cancel');
	} catch (Exception $e) {
		dataResponse(400, $e->getMessage());
	}
else:
	dataResponse(400, 'Missing param activity_id');
endif;
