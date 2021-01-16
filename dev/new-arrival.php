<?php
require_once '../app/Mage.php';
require_once 'functions.php';
if ($_REQUEST['current_page'] && $_REQUEST['current_page'] > 0) {
	$defaultLimit = 10;
	$currentPage = $_REQUEST['current_page'];
	/** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
	$collection = Mage::getModel('catalog/category')->load(3)->getProductCollection()
		->addAttributeToSelect('*')// add all attributes - optional
		->addAttributeToFilter('status', 1)// enabled
		->addAttributeToFilter('visibility', 4)//visibility in catalog,search
		->addAttributeToSort('entity_id', 'desc')
		->setPageSize($defaultLimit)
		->setCurPage($currentPage)
		->load();

	$data = array();
	$dataArr = array();
	foreach ($collection as $item):
		$data['id'] = $item->getId();
		$data['product_id'] = $item->getId();
		$data['name'] = $item->getName();
		$data['special_price'] = $item->getSpecialPrice() ? (double)$item->getSpecialPrice() : null;
		$data['price'] = (double)$item->getPrice();
		$data['image'] = $item->getImageUrl();
		$data['brand'] = $item->getResource()->getAttribute('carnival_brand')->getFrontend()->getValue($item);
		$dataArr['data'][] = $data;
	endforeach;
	$dataArr['totalProducts'] = $collection->getSize();
	http_response_code(200);
	echo json_encode(array('status_code' => 200, 'message' => 'valid', 'productData' => $dataArr));
} else {
	http_response_code(400);
	echo json_encode(array('status_code' => 400, 'message' => 'Invalid'));
}