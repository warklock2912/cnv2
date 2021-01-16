<?php
require_once '../app/Mage.php';
require_once 'functions.php';
checkIsLoggedIn();

$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);
$customerId = $data['user_id'];
$productId = $data['product_id'];
$wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerId, true);
$product = Mage::getModel('catalog/product')->load($productId);

$buyRequest = new Varien_Object(array()); // any possible options that are configurable and you want to save with the product

try{
	$result = $wishlist->addNewItem($product, $buyRequest);
	$wishlist->save();
	http_response_code(200);
	echo json_encode(array('status_code' => 200, 'message' => 'valid', 'succeed' => true));
}
catch (Exception $e){
	http_response_code(400);
	echo json_encode(array('status_code' => 400, 'message' => $e->getMessage()));
}
