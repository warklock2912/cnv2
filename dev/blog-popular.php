<?php
/**
 * Created by PhpStorm.
 * User: tuan
 * Date: 08/08/2018
 * Time: 14:58
 */

require_once '../app/Mage.php';
require_once 'functions.php';

if (isset($_REQUEST['current_page']) && $_REQUEST['current_page'] > 0) {
	$defaultLimit = 5;
	$currentPage = $_REQUEST['current_page'] ;
	$cpBlock = Mage::app()->getLayout()->getBlockSingleton('Magpleasure_Blog_Block_Content_List');

	$collection = Mage::getModel('mpblog/post')->getCollection();
	if (!Mage::app()->isSingleStoreMode()) {
		$collection->addStoreFilter(Mage::app()->getStore()->getId());
	}
	$collection->addFieldToFilter('status', Magpleasure_Blog_Model_Post::STATUS_ENABLED);
	$collection->setUrlKeyIsNotNull();
	$collection->getSelect()->order("main_table.views DESC");
	$collection->setPageSize($defaultLimit)
		->setCurPage($currentPage);
	if (isset($_REQUEST['id'])) {
		$categoriesID = $_REQUEST['id'];
		$collection->addCategoryFilter($categoriesID);
	}
	$total = $collection->getSize();
//$s = Mage::getSingleton('core/layout')->getBlock('mpblog.content.list');
//print_r($s->getCollection($collectionId));exit;
	$data = array();
	$dataArr = array();
	if (count($collection)) {
		foreach ($collection as $post): $i++;
			$data['post_id'] = $post->getId();
			$data['post_url'] = $post->getPostUrl();
			$_postTitleStripped = $cpBlock->escapeHtml($post->getTitle());
			$data['post_name'] = $_postTitleStripped;
			$data['post_image'] = $post->getListThumbnailSrc();
			$data['post_created'] = strtotime($post->getPublishedAt()) . '';
			$data['post_by'] = $post->getPostedBy();
			$data['post_view'] = $post->getViews();
			$categoryList = null;
			$categories = $cpBlock->getCategories($post->getId());
			foreach ($categories as $category):
				$categoryList[] = $category->getName();
			endforeach;
			$data['post_category'] = $categoryList;
			$dataArr[] = $data;
		endforeach;
		//var_dump($data);
		dataResponse(200, 'valid', $dataArr, 'postData',$total);
	} else {
		dataResponse(204, 'No Post');
	}

} else
	dataResponse(400, 'Missing param current_page');
