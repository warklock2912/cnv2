<?php
/**
 * Created by PhpStorm.
 * User: tuan
 * Date: 08/08/2018
 * Time: 14:58
 */

require_once '../app/Mage.php';
require_once 'functions.php';
function checkIsSelected($categoryId)
{
	if (isset($_REQUEST['customer_id'])) {
		$customerId = $_REQUEST['customer_id'];
		$notificationSetting = Mage::getModel('newsnotification/newsnotification')
			->getCollection()
			->addFieldToFilter('customer_id', $customerId)
			->addFieldToFilter('category_id', $categoryId);
		if (count($notificationSetting)) {
			return true;
		} else {
			return false;
		}
	}
	else return false;
}

$cpBlock = Mage::app()->getLayout()->getBlockSingleton('Magpleasure_Blog_Block_Sidebar_Category');
//$s = Mage::getSingleton('core/layout')->getBlock('mpblog.content.list');
//print_r($s->getCollection($collectionId));exit;
$data = array();
$dataArr = array();


if (count($cpBlock->getCollection())) {

    $cpBlock->getCollection()->getSelect()->order('sort_order ASC');

	foreach ($cpBlock->getCollection() as $category):
		if ($category->getCategoryForApp() == 1) {
			$data['category_id'] = $category->getId();
			$data['url_for_app'] = $category->getUrlForApp();
			$data['category_name'] = $cpBlock->escapeHtml($category->getName());
			//$data['category_for_app'] = $category->getCategoryForApp();
			$isSelected = checkIsSelected($category->getId());
			$data['is_selected'] = $isSelected;
			$data['image_for_app'] =  Mage::getBaseUrl().'media'.$category->getImageForApp();
			$data['category_description'] = $category->getDescription();
			$data['sort_order'] = $category->getSortOrder();
			$data['created_at'] = $category->getCreatedAt();
			$dataArr['category'][] = $data;
		} else {
			$data['category_id'] = $category->getId();
			$data['url_for_app'] = $category->getUrlForApp();
			$data['category_name'] = $cpBlock->escapeHtml($category->getName());
			//$data['category_for_app'] = $category->getCategoryForApp();
			$isSelected = checkIsSelected($category->getId());
			$data['is_selected'] = $isSelected;
			$data['image_for_app'] = $category->getImageForApp()?  Mage::getBaseUrl().'media'.$category->getImageForApp():null;
			$data['category_description'] = $category->getDescription();
			$data['sort_order'] = $category->getSortOrder();
			$data['created_at'] = $category->getCreatedAt();
			$dataArr['brand'][] = $data;
		}
	endforeach;
	//var_dump($data);
	http_response_code(200);
	echo json_encode(array('status_code' => 200, 'message' => 'valid', 'postData' => $dataArr));
} else {
	http_response_code(200);
	echo json_encode(array('status_code' => 200, 'message' => 'No Post'));
}
