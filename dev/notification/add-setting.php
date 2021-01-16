<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);

if ($data['customer_id']) {
	$customerId = $data['customer_id'];
	$addListCategoryId = $data['add_categories'];
	$deleteListCategoryId = $data['delete_categories'];
	foreach ($addListCategoryId as $categoryId) {
		$newsNotification = Mage::getModel('newsnotification/newsnotification');
		$checkNewsNotification = Mage::getModel('newsnotification/newsnotification')->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('category_id', $categoryId);
        ;
		if(!count($checkNewsNotification))
		$newsNotification->setCategoryId($categoryId)->setCustomerId($customerId)->save();
	}


	$deleteListNotification = Mage::getModel('newsnotification/newsnotification')->getCollection()
		->addFieldToFilter('customer_id', $customerId)
		->addFieldToFilter('category_id', array('in' => $deleteListCategoryId));

	foreach ($deleteListNotification as $newsNotification) {
		$newsNotification->delete();
	}
	dataResponse(200, 'successfully');
} else {
	dataResponse(400, 'Invalid Post');
}