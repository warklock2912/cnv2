<?php
/**
 * Created by PhpStorm.
 * User: tuan
 * Date: 9/7/18
 * Time: 1:53 PM
 */

require_once '../app/Mage.php';
require_once 'functions.php';
//Mage::getSingleton("core/session", array("name" => "frontend"));

$blockId = 3;
$imageModel = Mage::getModel('bannerads/images');
$blockImage = Mage::getResourceModel('bannerads/bannerads')->lookupImagesId($blockId);
$images = $imageModel->getCollection()
    ->addFieldToFilter('banner_id', array('in' => $blockImage))
    ->addFieldtoFilter('start_time',
        array(
            array('to' => Mage::getModel('core/date')->gmtDate()),
            array('start_time', 'null' => '')
        )
    )
    ->addFieldtoFilter('end_time',
        array(
            array('gteq' => Mage::getModel('core/date')->gmtDate()),
            array('end_time', 'null' => '')
        )
    )
    ->addFieldToFilter('status', 1)->setOrder('sort_order', "ASC");

    //$images->getSelect()->order('rand()');
    //$images->setPageSize(1);



$data = array();
$dataArr = array();

$dataMarge = array();
if (count($images)) {
    foreach ($images as $image) {
        $data['banner_id'] = $image->getBannerId();
        $data['banner_title'] = $image->getBannerTitle();
        $data['banner_image'] = Mage::getBaseUrl('media') . "banners/images/" . $image->getBannerImage();
        $data['banner_url'] = $image->getBannerUrl();
        $data['banner_description'] = $image->getBannerDescription();
        $data['status'] = $image->getStatus();
        $data['created_time'] = $image->getCreatedTime();
        $data['banner_show_desc'] = $image->getBannerShowDesc();
        $dataArr[] = $data;
    }

    $dataMarge['banner'] = $dataArr;
    $dataMarge['product_new_in'] = getProductsForFeature(68);
    $dataMarge['product_adidas'] = getProductsForFeature(69);
    //get HighLight Brands

    $_helper = Mage::helper('catalog/category');
    $categoryBrandId = 4;
    $category = Mage::getModel('catalog/category')->load($categoryBrandId);
    $_categories = $category->getChildrenCategories();

    if ($category->getId() != null) {
        $parent['category_id'] = $category->getId();
        $parent['parent_id'] = $category->getParentId();
        $parent['name'] = $category->getName();
        $parent['is_active'] = $category->getIsActive();
        $parent['position'] = $category->getPosition();
        $parent['level'] = $category->getLevel();
        $parent['children'] = display_children($_categories, 0);
        $parent['images'] = $category->getImageUrl();
        $parent['include_in_menu'] = $category->getIncludeInMenu();
    }
    $dataMarge['highlight_brand'] = $parent;
    $dataMarge['product_weekly_special'] = getProductsForFeature(70);
    $dataMarge['product_herschel'] = getProductsForFeature(71);
    http_response_code(200);

    echo json_encode(array('status_code' => 200, 'message' => 'valid', 'postData' => $dataMarge));
}else {
    http_response_code(204);
    echo json_encode(array('status_code' => 204, 'message' => 'No Banner'));
}

