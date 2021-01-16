<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();
$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);
if ($data['customer_id']) {
	$customerId = $data['customer_id'];
	$cardId = isset($data['card_id']) ? $data['card_id'] : null;
	$phone = isset($data['telephone']) ? $data['telephone'] : null;
	$customerCard = Mage::getModel('activity/activity');
	$collection = $customerCard->getCollection();
	$customer = Mage::getModel('customer/customer')->load($customerId);
	if (!$customer) {
		dataResponse(400, 'No customer found');
		return;
	}
	$customer->setTelephone($phone);
	$customer->save();
	$collectionFilterByCardId = $collection->addFieldToFilter('card_id', $cardId);
	if (count($collectionFilterByCardId)) {
		dataResponse(400, 'Card Id is exist');
		return;
	}

	$collectionFilterByCustomerId = Mage::getModel('activity/activity')
		->getCollection()
		->addFieldToFilter('customer_id', $customerId);
	if (count($collectionFilterByCustomerId)) {
		$customerCard = $collectionFilterByCustomerId->getFirstItem()->setCardId($cardId);
	} else {
		$customerCard->setCustomerId($customerId)->setCardId($cardId);
	}

	try {
		$customerCard->save();
		http_response_code(200);
		echo json_encode(array('status_code' => 200, 'message' => 'successfully', 'postData' => $customerCard->getData()));
	} catch (Exception $e) {
		http_response_code(400);
		echo json_encode(array('status_code' => 400, 'message' => $e->getMessage()));
	}
} else {
	http_response_code(400);
	echo json_encode(array('status_code' => 400, 'message' => 'invalid post'));
}