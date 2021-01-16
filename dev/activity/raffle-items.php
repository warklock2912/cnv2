<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();
$dataArr = array();
$statusCode = 200;
$message = '';
$data = array();
if ($_REQUEST['activity_id']) {
	$campaignId = $_REQUEST['activity_id'];
	$raffleItems = Mage::getModel('campaignmanage/products')->getCollection()
		->addFieldToFilter('campaign_id', $campaignId);
	$productIds = array();
	foreach ($raffleItems as $raffleItem) {
		$productIds[] = $raffleItem->getProductId();
	}

	$products = Mage::getModel('catalog/product')->getCollection()
		->addFieldToFilter('entity_id', array('in' => $productIds))
		->addAttributeToSelect('*')
		->load();

	foreach ($products as $product) {
        $stock = false;
        if($product->isSaleable()){
            $stock = true;
        }
		$data['product_id'] = $product->getId();
		$data['sku'] = $product->getSku();
		$data['name'] = $product->getName();
		$data['image'] = $product->getImageUrl();
		$data['price'] = (string)$product->getPrice();
		$data['special_price'] = $product->getSpecialPrice() ? (string)$product->getSpecialPrice() : null;
		$data['brand'] = $product->getResource()->getAttribute('carnival_brand')->getFrontend()->getValue($product);
        $data['in_stock'] = $stock;

        $dataArr[] = $data;
	}
	$message = 'valid';
} else {
	$statusCode = 400;
	$message = 'Missing activity_id';
}

dataResponse($statusCode, $message, $dataArr,'productList');