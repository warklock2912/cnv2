<?php
require_once '../app/Mage.php';
require_once 'functions.php';
checkIsLoggedIn();


if ($_REQUEST['id']) {
	$customerId = $_REQUEST['id'];

	$wishList = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerId);
	$wishListItemCollection = $wishList->getItemCollection();

	$data = array();
	$dataArr = array();
	if (count($wishListItemCollection)) {

		$arrProductIds = array();
		foreach ($wishListItemCollection as $item):
			$productId = $item->getProductId();
			$product = Mage::getModel('catalog/product')->load($productId);
			$data['product_id'] = $product->getId();
			$data['name'] = $product->getName();
			$data['special_price'] = $product->getSpecialPrice() ? (string)$product->getSpecialPrice() : null;
			$data['price'] = (string)$product->getPrice();
			$data['image'] = $product->getImageUrl();
			$data['brand'] = $product->getResource()->getAttribute('carnival_brand')->getFrontend()->getValue($product);

			$dataArr[] = $data;
		endforeach;

		http_response_code(200);
		echo json_encode(array('status_code' => 200, 'message' => 'valid', 'wishlistData' => $dataArr));
	} else {

		http_response_code(200);
		echo json_encode(array('status_code' => 200, 'message' => 'No Item', 'wishlistData' => $dataArr));
	}
} else {
	http_response_code(400);
	echo json_encode(array('status_code' => 400, 'message' => 'Invalid '));
}
