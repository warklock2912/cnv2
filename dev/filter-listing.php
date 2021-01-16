<?php

require_once '../app/Mage.php';
require_once 'functions.php';

try {
    $dataFilter = json_decode(file_get_contents("php://input"), true);
    $categoryId = isset($dataFilter['category_id']) ? $dataFilter['category_id'] : 2;

    Mage::app('default');
//    Mage::getSingleton("core/session", array("name" => "frontend"));
    $websiteId = Mage::app()->getWebsite()->getId();
    $store = Mage::app()->getStore();

    $layer = Mage::getModel("catalog/layer");
    $category = Mage::getModel('catalog/category')->load($categoryId);
    Mage::register('current_category_filter', $category, true);
    $layer->setCurrentCategory($category);
    $attributes = $layer->getFilterableAttributes();

    foreach ($attributes as $attribute) {
        $filter_attr = array();
        $filter_attr['title'] = $attribute->getFrontendLabel();
        $filter_attr['code'] = $attribute->getAttributeCode();

        if ($attribute->getAttributeCode() == 'price') {
            $filterBlockName = 'catalog/layer_filter_price';
        }elseif ($attribute->getBackendType() == 'decimal') {
            $filterBlockName = 'catalog/layer_filter_decimal';
        }else {
            $filterBlockName = 'catalog/layer_filter_attribute';
        }

        $result =  Mage::app()->getLayout()->createBlock($filterBlockName)->setLayer($layer)->setAttributeModel($attribute)->init();
        $i=0;

        foreach($result->getItems() as $option) {
            $attr_option = array();
            if($attribute->getAttributeCode() == 'price') {
                $attr_option['label'] = str_replace(array('<span class=\'p_bath\' style=\'margin-left :2px\' >','</span>', '<span class="price">'),'',$option->getLabel());
            } else {
                $attr_option['label'] = $option->getLabel();
            }

            $attr_option['value'] = $option->getValue();
            $attr_option['count'] = $option->getCount();
            $i++;
            $filter_attr['options'][] = $attr_option;
        }

        if($i!=0){
            $filter_attributes[] = $filter_attr;
        }
    }

    $products = $layer->getProductCollection()->addAttributeToSelect('*');

    $result = array();

    // add order listing to filter data
    // add order listing to filter data
    $orderOptionsAvailable = $category->getAvailableSortByOptions();
    $orderOptions =array();
    foreach ($orderOptionsAvailable as $key => $value){
        $order['label'] = $value;
        $order['value'] = $key;
        $orderOptions[] = $order;
    }
    $result['OderBy'] = ['code' => 'order', 'options'=>$orderOptions];
    $result['filters'] = $filter_attributes;

    // get list category
    $key = $layer->getStateKey().'_SUBCATEGORIES';
    $data = $layer->getAggregator()->getCacheData($key);

    if ($data === null) {
        /** @var $categoty Mage_Catalog_Model_Categeory */
        $categories = $category->getChildrenCategories();

        $layer->getProductCollection()
            ->addCountToCategories($categories);

        $data = array();
        foreach ($categories as $category) {
            if ($category->getIsActive() && $category->getProductCount()) {
                $data[] = array(
                    'label' => Mage::helper('core')->escapeHtml($category->getName()),
                    'value' => $category->getId(),
                    'count' => $category->getProductCount(),
                );
            }
        }

        $tags = $layer->getStateTags();
        $layer->getAggregator()->saveCacheData($data, $key, $tags);
    }
    $result['category'] = $data;
    http_response_code(200);
    echo json_encode(array('status_code' => 200, 'message' => 'valid', 'data' => $result));
    exit;
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(array('status_code' => 400, 'message' => $e->getMessage()));
}