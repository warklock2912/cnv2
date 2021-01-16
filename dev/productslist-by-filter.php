<?php

require_once '../app/Mage.php';
require_once 'functions.php';
try {
    Mage::getSingleton("core/session", array("name" => "frontend"));
} catch (Exception $e) {
    sessionExpiredResult();
    die();
}

try {
    $dataFilter = json_decode(file_get_contents("php://input"), true);
    $categoryId = isset($dataFilter['category_id']) ? $dataFilter['category_id'] : 2;
    $pageSize = isset($dataFilter['limit']) ? $dataFilter['limit'] : 48;
    $pageNo = isset($dataFilter['p']) ? $dataFilter['p'] : 1;
    $dir = isset($dataFilter['dir']) ? $dataFilter['dir'] : 'DESC';

    $categoryIds = array();
    $activeRules = Mage::helper('amgroupcat')->getActiveRules();
    $hasVip = false;
    $customer = getCustomer();
    if ($customer && $customer->getId() != '' && $customer->getVipMemberId() != '') {
        $hasVip = true;
    }

    if (!empty($activeRules)) {
        foreach ($activeRules as $rule) {
            if ($hasVip == true && strtolower($rule['rule_name']) == 'vip member') {
                $categoryIds = Mage::helper('amgroupcat')->getRestrictedCategories($rule);
            } elseif ($hasVip == false && strtolower($rule['rule_name']) == 'normal') {
                $categoryIds = Mage::helper('amgroupcat')->getRestrictedCategories($rule);
            }
        }
    }

    $upcommingCategory = Mage::getStoreConfig('mobile_configuration/block6/category');
    if ($categoryId == $upcommingCategory) {
        $category = Mage::getModel('catalog/category')->load($upcommingCategory);
        $fromDate = $category->getData('counting_downs');
        $timezone = Mage::getStoreConfig('general/locale/timezone');
        $fromDate = new DateTime($fromDate, new DateTimeZone($timezone));
        /* Converts to UTC/GMT time zone */
        $fromDate = $fromDate->format('U');
        /* Formats datetime in UTC/GMT timezone to string */
        //$fromDate = date("Y-m-d H:i:s",$fromDate);
        $countdownTime = $fromDate;
    } else {
        $countdownTime = 0;
    }


    Mage::app('default');
    $websiteId = Mage::app()->getWebsite()->getId();
    $store = Mage::app()->getStore();

    $layer = Mage::getModel("catalog/layer");
    $category = Mage::getModel('catalog/category')->load($categoryId);
    Mage::register('current_category', $category);
    Mage::register('current_entity_key', $category->getPath());
    $layer->setCurrentCategory($category);
    $products = $layer->getProductCollection()->addAttributeToSelect('*');

    /*Mage::getSingleton('cataloginventory/stock')
        ->addInStockFilterToCollection($products);*/

    $parentCategory = Mage::getModel('catalog/category')->load($categoryId)->getParentId();

    //    if ($parentCategory == 4) {
    //        $products->addAttributeToFilter('type_id', array('eq' => 'configurable'));
    //    }

    $products->addFinalPrice();

    $availableOrders = $category->getAvailableSortByOptions();

    // default sort of magento
    $order = isset($dataFilter['order']) ? $dataFilter['order'] : $category->getDefaultSortBy();

    foreach ($dataFilter as $index => $item) {
        if ($index == 'category_id' || $index == 'order' || $index == 'dir' || $index == 'mode' || $index == 'p' || $index == 'limit' || $index == 'mode') {
            unset($dataFilter[$index]);
        }
    }
    $data['filters'] = $dataFilter;

    switch ($order) {
        case 'created_at':
            $products->getSelect()->order(array('created_at desc', 'cat_index_position desc', 'entity_id desc'));
            break;
        case 'position':
            $products->getSelect()->order(array('cat_index_position asc', 'entity_id desc'));
            //$products->getSelect()->order(array('cat_index_position asc', 'entity_id desc'));
            break;
        case 'name':
            $products->addAttributeToSort('name', 'ASC');
            break;
        case 'price':
            $products->getSelect()->order(array('price_index.min_price asc', 'entity_id desc'));
            break;
        case 'saving':
            $alias = 'price_index';
            if (preg_match('/`([a-z0-9\_]+)`\.`final_price`/', $products->getSelect()->__toString(), $m)) {
                $alias = $m[1];
            }

            $storeId = Mage::app()->getStore()->getId();
            if (Mage::getStoreConfig('amsorting/general/saving', $storeId)) {
                $products->getSelect()->columns(array('saving' => "IF(`$alias`.price, ((`$alias`.price - IF(`$alias`.tier_price IS NOT NULL, LEAST(`$alias`.min_price, `$alias`.tier_price), `$alias`.min_price)) * 100 / `$alias`.price), 0)"));
            } else {
                $products->getSelect()->columns(array('saving' => "(`$alias`.price - IF(`$alias`.tier_price IS NOT NULL, LEAST(`$alias`.min_price, `$alias`.tier_price), `$alias`.min_price))"));
            }
            $products->getSelect()->order(array('saving desc', 'cat_index_position desc', 'entity_id desc'));
            break;
        default:
            $products->getSelect()->order(array($category->getDefaultSortBy() . ' desc'));
            break;
    }


    $minPrice = 0;
    $maxPrice = 0;
    if (array_key_exists('filters', $data)) {
        foreach ($data['filters'] as $key => $item) {
            $items = array();
            if ($key == 'price') {
                $priceRanges = explode(',', $item);
                foreach ($priceRanges as $priceRange) {
                    $priceLimit = explode('-', $priceRange);
                    $minPrice = $priceLimit[0];
                    $maxPrice = $priceLimit[1];
                }
            } else {
                $items = explode(',', $item);
                $attribute = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $key);

                $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
                $tableAlias = $attribute->getAttributeCode() . '_idx';

                $conditions = array(
                    "{$tableAlias}.entity_id = e.entity_id",
                    $connection->quoteInto(
                        "{$tableAlias}.attribute_id = ?",
                        $attribute->getAttributeId()
                    ),
                    $connection->quoteInto("{$tableAlias}.store_id = ?", 1),
                    $connection->quoteInto("{$tableAlias}.value IN (?)", $items)
                );

                $products->getSelect()->join(
                    array($tableAlias => Mage::getResourceModel('catalog/layer_filter_attribute')->getMainTable()),
                    implode(' AND ', $conditions),
                    array()
                );
            }
        }
        $products->addFinalPrice();
        if ($maxPrice != 0) {
            //$products->getSelect()->where('price_index.final_price <= ' . $maxPrice)->where('price_index.final_price >= ' . $minPrice);
            $products->getSelect()->where('price_index.price <= ' . $maxPrice)->where('price_index.price >= ' . $minPrice);
            $price['minPrice'] = (int)$minPrice;
            $price['maxPrice'] = (int)$maxPrice;
            $json['priceRangeApplied'] = $price;
        }
    }

    $totalProducts = $products->getSize();
    $products->setPageSize($pageSize)
        ->setCurPage($pageNo);

    $i = 0;
    $flag = 0;
    $response = array();
    $label_collection = Mage::getModel('amlabel/label')->getCollection()
        ->addFieldToFilter('include_type', array('neq' => 1));
    foreach ($products as $_product) {
        //var_dump($_product->getId());
        $labels = array();
        if (0 < $label_collection->getSize()) {
            foreach ($label_collection as $label) {
                $name = 'amlabel_' . $label->getId();
                if ($_product->hasData($name)) {
                    $labels[] = $label->getId();
                } elseif ($_product->getData('sku')) {
                    $skus = explode(',', $label->getIncludeSku());
                    if (in_array($_product->getData('sku'), $skus)) {
                        $labels[] = $label->getId();
                    }
                }
            }
        }
        $stock = false;
        if ($_product->isSaleable()) {
            $stock = true;
        }
        $buy_with_point = false;
        $points_spend = 0;
        if ($_product->getResource()->getAttribute('rewardpoints_spend')->getFrontend()->getValue($_product)) {
            $buy_with_point = true;
            $points_spend = (int)$_product->getResource()->getAttribute('rewardpoints_spend')->getFrontend()->getValue($_product);
        }
        $response[$i] = array(
            'id' => $_product->getId(),
            'product_id' => $_product->getId(),
            'name' => $_product->getName(),
            'price' => $_product->getPrice(),
            'special_price' => $_product->getFinalPrice(),
            'image' => $_product->getImageUrl(),
            'brand' => $_product->getAttributeText('carnival_brand'),
            'url' => $_product->getProductUrl(),
            'in_stock' => $stock,
            'buy_with_point' => $buy_with_point,
            'points_spend' => $points_spend,
            'label' => $labels,
            'created_at' => $_product->getData('created_at'),
        ); //give what ever values you need.
        $i++;
    }
    $bestprodts = $response;

    //$result = array();
    $result['status'] = 1;
    if (!$totalProducts) {
        if ($minPrice == 0 && $maxPrice == 0) {
            $result['message'] = 'No products available in this category.';
        } else {
            $result['message'] = 'No products found. Please check the filter.';
        }
    } else {
        $result['message'] = 'Success';
    }
    $result['totalProducts'] = $totalProducts;
    $result['data']['productList'] = $bestprodts;
    $result['countdownTime'] = $countdownTime;
    $result['default_sort'] = $category->getDefaultSortBy();
    $result['categoryName'] = $category->getName();

    if (count($categoryIds) > 0 && !in_array($categoryId, $categoryIds)) {
        $result['redirect_url'] = 404;
    }

    http_response_code(200);
    echo json_encode(array('status_code' => 200, 'message' => 'valid') + $result);
    exit;
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(array('status_code' => 400, 'message' => $e->getMessage()));
}
