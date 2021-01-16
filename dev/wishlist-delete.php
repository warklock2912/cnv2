<?php
require_once '../app/Mage.php';
require_once 'functions.php';
checkIsLoggedIn();

//Mage::getSingleton("core/session", array("name" => "frontend"));

$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);
$customerId = $data['customer_id'];
$ids = $data['ids'];
$wishListId = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerId)->getId();
if ($ids && $wishListId) {
	try {
		foreach ($ids as $id):
			Mage::getModel('wishlist/item')
				->getCollection()
				->addFieldToFilter('product_id', $id)
				->addFieldToFilter('wishlist_id', $wishListId)
				->getFirstItem()
				->delete();
		endforeach;
		http_response_code(200);
		echo json_encode(array('status_code' => 200, 'message' => 'valid', 'succeed' => 'true'));
	} catch (Exception $e) {
		http_response_code(400);
		echo json_encode(array('status_code' => 400, 'message' => 'invalid', 'succeed' => $e->getMessage()));
	}
} else {
	http_response_code(400);
	echo json_encode(array('status_code' => 400, 'message' => 'invalid Post'));
}

