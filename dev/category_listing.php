<?php

/**
 * Created by PhpStorm.
 * User: tuan
 * Date: 16/08/2018
 * Time: 09:15
 */

require_once '../app/Mage.php';
require_once 'functions.php';
Mage::getSingleton("core/session", array("name" => "frontend"));

$categoryIds = array();
$activeRules = Mage::helper('amgroupcat')->getActiveRules();/* active rules which have "remove_category_links" flag */

$hasVip = false;
$customer = getCustomer();
if ($customer && $customer->getId() != '' && $customer->getVipMemberId() != '') {
	$hasVip = true;
}

if (!empty($activeRules)) {
	foreach ($activeRules as $rule) {
		if ($hasVip == true && strtolower($rule['rule_name']) == 'vip member') {
			$categoryIds = Mage::helper('amgroupcat')->getRestrictedCategories($rule);
			// break;
		} elseif ($hasVip == false && strtolower($rule['rule_name']) == 'normal') {
			$categoryIds = Mage::helper('amgroupcat')->getRestrictedCategories($rule);
			// break;
		}
	}
}
if (count($categoryIds) > 0) {
	try {
		$cacheId = 'mobile_category_listing';
		$cacheTag = 'block_html';
		$parentCategory = array();
		$_helper = Mage::helper('catalog/category');

		$shopByCategory = Mage::getModel('catalog/category')->load(5);
		$_categories = $shopByCategory->getChildrenCategories();
		$dataArr = array();
		if ($shopByCategory->getId() != null) {
			$subcategories = Mage::getModel('catalog/category')->getCollection()
				->addAttributeToSelect('*')
				->addFieldToFilter('parent_id', $shopByCategory->getId())
				->addIdFilter(implode(",", $categoryIds))
				->addAttributeToSort('position', 'ASC');
			$parentCategory['category_id'] = $shopByCategory->getId();
			$parentCategory['parent_id'] = (string)$shopByCategory->getParentId();
			$parentCategory['name'] = $shopByCategory->getName();
			$parentCategory['is_active'] = $shopByCategory->getIsActive();
			$parentCategory['is_include_navigation_menu'] = $shopByCategory->getIncludeInMenu() == 1 ? true : false;
			$parentCategory['children'] = display_children($subcategories, 0);
			$parentCategory['images'] = (string)$shopByCategory->getImageUrl();
			$parentCategory['reference_category'] = $shopByCategory->getData('reference_category');
			$dataArr['Shop_By_Categories'] = $parentCategory;
		}
		$shopByBrand = Mage::getModel('catalog/category')->load(4);
		$parentBrand = array();
		if ($shopByBrand->getId() != null) {
			$subcategories = Mage::getModel('catalog/category')->getCollection()
				->addAttributeToSelect('*')
				->addFieldToFilter('parent_id', $shopByBrand->getId())
				->addIdFilter(implode(",", $categoryIds))
				->addAttributeToSort('position', 'ASC');
			$parentBrand['category_id'] = $shopByBrand->getId();
			$parentBrand['parent_id'] = (string)$shopByBrand->getParentId();
			$parentBrand['name'] = $shopByBrand->getName();
			$parentBrand['is_active'] = $shopByBrand->getIsActive();
			$parentBrand['is_include_navigation_menu'] = $shopByBrand->getIncludeInMenu() == 1 ? true : false;
			$parentBrand['children'] = display_children($subcategories, 0);
			$parentBrand['images'] = (string)$shopByBrand->getImageUrl();
			$parentBrand['reference_category'] = $shopByBrand->getData('reference_category');
			$dataArr['Shop_By_Brand'] = $parentBrand;
		}


		$highlight_brands = Mage::getResourceModel('catalog/category_collection')
			->addFieldToFilter('name', 'Hightlight Brands')
			->getFirstItem();

		if (!$highlight_brands->getId()) {
			$highlight_brands = Mage::getModel('catalog/category')->load(124);
		} else {
			$highlight_brands = Mage::getModel('catalog/category')->load($highlight_brands->getId());
		}

		if ($highlight_brands->getId() != null) {
			$subcategories = Mage::getModel('catalog/category')->getCollection()
				->addAttributeToSelect('*')
				->addFieldToFilter('parent_id', $highlight_brands->getId())
				->addAttributeToSort('position', 'ASC');

			$parentCategory['category_id'] = $highlight_brands->getId();
			$parentCategory['parent_id'] = (string)$highlight_brands->getParentId();
			$parentCategory['name'] = $highlight_brands->getName();
			$parentCategory['is_active'] = $highlight_brands->getIsActive();
			$parentCategory['children'] = display_children($subcategories, 0);
			$parentCategory['images'] = (string)$highlight_brands->getImageUrl();
			$parentCategory['reference_category'] = $highlight_brands->getData('reference_category');
			$dataArr['highlight_brands'] = $parentCategory;
		}


		$data_to_be_cached = $dataArr;
		//		Mage::app()->getCache()->save(serialize($data_to_be_cached), $cacheId, array($cacheTag));
		//	}
		http_response_code(200);
		echo json_encode(array('status_code' => 200, 'message' => 'valid', 'postData' => $data_to_be_cached));
	} catch (Exception $e) {
		http_response_code(400);
		echo json_encode(array('status_code' => 400, 'message' => $e->getMessage()));
	}
} else {
	try {
		$cacheId = 'mobile_category_listing';
		$cacheTag = 'block_html';
		$parentCategory = array();
		//	if (($data_to_be_cached = Mage::app()->getCache()->load($cacheId))) {
		//		$data_to_be_cached = unserialize($data_to_be_cached);
		//	} else {
		$_helper = Mage::helper('catalog/category');

		$shopByCategory = Mage::getModel('catalog/category')->load(5);
		$_categories = $shopByCategory->getChildrenCategories();
		$dataArr = array();
		if ($shopByCategory->getId() != null) {
			$subcategories = Mage::getModel('catalog/category')->getCollection()
				->addAttributeToSelect('*')
				->addFieldToFilter('parent_id', $shopByCategory->getId())
				->addAttributeToSort('position', 'ASC');
			$parentCategory['category_id'] = $shopByCategory->getId();
			$parentCategory['parent_id'] = (string)$shopByCategory->getParentId();
			$parentCategory['name'] = $shopByCategory->getName();
			$parentCategory['is_active'] = $shopByCategory->getIsActive();
			$parentCategory['is_include_navigation_menu'] = $shopByCategory->getIncludeInMenu() == 1 ? true : false;
			$parentCategory['children'] = display_children($subcategories, 0);
			$parentCategory['images'] = (string)$shopByCategory->getImageUrl();
			$parentCategory['reference_category'] = $shopByCategory->getData('reference_category');
			$dataArr['Shop_By_Categories'] = $parentCategory;
		}
		$shopByBrand = Mage::getModel('catalog/category')->load(4);
		$parentBrand = array();
		if ($shopByBrand->getId() != null) {
			$subcategories = Mage::getModel('catalog/category')->getCollection()
				->addAttributeToSelect('*')
				->addFieldToFilter('parent_id', $shopByBrand->getId())
				->addAttributeToSort('position', 'ASC');
			$parentBrand['category_id'] = $shopByBrand->getId();
			$parentBrand['parent_id'] = (string)$shopByBrand->getParentId();
			$parentBrand['name'] = $shopByBrand->getName();
			$parentBrand['is_active'] = $shopByBrand->getIsActive();
			$parentBrand['is_include_navigation_menu'] = $shopByBrand->getIncludeInMenu() == 1 ? true : false;
			$parentBrand['children'] = display_children($subcategories, 0);
			$parentBrand['images'] = (string)$shopByBrand->getImageUrl();
			$parentBrand['reference_category'] = $shopByBrand->getData('reference_category');
			$dataArr['Shop_By_Brand'] = $parentBrand;
		}


		$highlight_brands = Mage::getResourceModel('catalog/category_collection')
			->addFieldToFilter('name', 'Hightlight Brands')
			->getFirstItem();

		if (!$highlight_brands->getId()) {
			$highlight_brands = Mage::getModel('catalog/category')->load(124);
		} else {
			$highlight_brands = Mage::getModel('catalog/category')->load($highlight_brands->getId());
		}

		if ($highlight_brands->getId() != null) {
			$subcategories = Mage::getModel('catalog/category')->getCollection()
				->addAttributeToSelect('*')
				->addFieldToFilter('parent_id', $highlight_brands->getId())
				->addAttributeToSort('position', 'ASC');

			$parentCategory['category_id'] = $highlight_brands->getId();
			$parentCategory['parent_id'] = (string)$highlight_brands->getParentId();
			$parentCategory['name'] = $highlight_brands->getName();
			$parentCategory['is_active'] = $highlight_brands->getIsActive();
			$parentCategory['children'] = display_children($subcategories, 0);
			$parentCategory['images'] = (string)$highlight_brands->getImageUrl();
			$parentCategory['reference_category'] = $highlight_brands->getData('reference_category');
			$dataArr['highlight_brands'] = $parentCategory;
		}


		$data_to_be_cached = $dataArr;
		//		Mage::app()->getCache()->save(serialize($data_to_be_cached), $cacheId, array($cacheTag));
		//	}
		http_response_code(200);
		echo json_encode(array('status_code' => 200, 'message' => 'valid', 'postData' => $data_to_be_cached));
	} catch (Exception $e) {
		http_response_code(400);
		echo json_encode(array('status_code' => 400, 'message' => $e->getMessage()));
	}
}
