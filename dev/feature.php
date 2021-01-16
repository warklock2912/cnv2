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


    $dataMarge = array();
    $data_images = array();

//block 1
    $data_block_1['isActive'] = Mage::getStoreConfig("mobile_configuration/block1/enable");
    $data_block_1['type'] = Mage::getStoreConfig("mobile_configuration/block1/type");
    $data_block_1['position'] = Mage::getStoreConfig("mobile_configuration/block1/position");
    $data_block_1['banner_type'] = Mage::getStoreConfig("mobile_configuration/block1/banner_type");
    $block_ads_id = Mage::getStoreConfig("mobile_configuration/block1/banner_ads");
    assign_banner_images($data_block_1, $block_ads_id);
    $dataMarge[] = $data_block_1;

//block 2
    $data_block_2['isActive'] = Mage::getStoreConfig("mobile_configuration/block2/enable");
    $data_block_2['type'] = Mage::getStoreConfig("mobile_configuration/block2/type");
    $data_block_2['position'] = Mage::getStoreConfig("mobile_configuration/block2/position");
    $data_block_2['banner_type'] = Mage::getStoreConfig("mobile_configuration/block2/banner_type");
    $block_ads_id = Mage::getStoreConfig("mobile_configuration/block2/banner_ads");
    assign_banner_images($data_block_2, $block_ads_id);
    $dataMarge[] = $data_block_2;

//block 3
    $data_block_3['text'] = Mage::getStoreConfig("mobile_configuration/block3/text");
    $data_block_3['isActive'] = Mage::getStoreConfig("mobile_configuration/block3/enable");
    $data_block_3['type'] = Mage::getStoreConfig("mobile_configuration/block3/type");
    $data_block_3['position'] = Mage::getStoreConfig("mobile_configuration/block3/position");
    $data_block_3['banner_type'] = Mage::getStoreConfig("mobile_configuration/block3/banner_type");
    $block_ads_id = Mage::getStoreConfig("mobile_configuration/block3/banner_ads");
    assign_banner_images($data_block_3, $block_ads_id);
    $dataMarge[] = $data_block_3;

//block 4
    $data_block_4['text'] = Mage::getStoreConfig("mobile_configuration/block4/text");
    $data_block_4['isActive'] = Mage::getStoreConfig("mobile_configuration/block4/enable");
    $data_block_4['type'] = Mage::getStoreConfig("mobile_configuration/block4/type");
    $data_block_4['position'] = Mage::getStoreConfig("mobile_configuration/block4/position");
    $data_block_4['banner_type'] = Mage::getStoreConfig("mobile_configuration/block4/banner_type");
    $block_ads_id = Mage::getStoreConfig("mobile_configuration/block4/banner_ads");
    assign_banner_images($data_block_4, $block_ads_id);
    $dataMarge[] = $data_block_4;

//block 5
    $data_block_5['text'] = Mage::getStoreConfig("mobile_configuration/block5/text");
    $data_block_5['isActive'] = Mage::getStoreConfig("mobile_configuration/block5/enable");
    $data_block_5['type'] = Mage::getStoreConfig("mobile_configuration/block5/type");
    $data_block_5['position'] = Mage::getStoreConfig("mobile_configuration/block5/position");
    $data_block_5['banner_type'] = Mage::getStoreConfig("mobile_configuration/block5/banner_type");
    $block_ads_id = Mage::getStoreConfig("mobile_configuration/block5/banner_ads");
    assign_banner_images($data_block_5, $block_ads_id);
    $dataMarge[] = $data_block_5;

//block 6
    $data_block_6['text'] = Mage::getStoreConfig("mobile_configuration/block6/text");
    $data_block_6['isActive'] = Mage::getStoreConfig("mobile_configuration/block6/enable");
    $data_block_6['type'] = Mage::getStoreConfig("mobile_configuration/block6/type");
    $data_block_6['position'] = Mage::getStoreConfig("mobile_configuration/block6/position");
    $category_id = Mage::getStoreConfig("mobile_configuration/block6/category");
    if ($category_id) {
        $data_block_6['category_id'] = $category_id;
        $data_block_6['products'] = getProductsForFeature($category_id);
        $category = Mage::getModel('catalog/category')->load($category_id);

        //$fromDate = '12/11/2018 4:00 PM';
        $fromDate = $category->getData('counting_downs');
        $timezone = Mage::getStoreConfig('general/locale/timezone');
        $fromDate = new DateTime($fromDate, new DateTimeZone($timezone));
        /* Converts to UTC/GMT time zone */
        $fromDate = $fromDate->format('U');
        /* Formats datetime in UTC/GMT timezone to string */
        //$fromDate = date("Y-m-d H:i:s",$fromDate);

        $data_block_6['countdownTime'] = $fromDate;
    }
    $dataMarge[] = $data_block_6;

//block 7
    $data_block_7['text'] = Mage::getStoreConfig("mobile_configuration/block7/text");
    $data_block_7['isActive'] = Mage::getStoreConfig("mobile_configuration/block7/enable");
    $data_block_7['type'] = Mage::getStoreConfig("mobile_configuration/block7/type");
    $data_block_7['position'] = Mage::getStoreConfig("mobile_configuration/block7/position");
    $category_id = Mage::getStoreConfig("mobile_configuration/block7/category");
    if ($category_id) {
        $data_block_7['category_id'] = $category_id;
        $data_block_7['products'] = getProductsForFeature($category_id);
    }
    $dataMarge[] = $data_block_7;


//block 8
    $data_block_8['isActive'] = Mage::getStoreConfig("mobile_configuration/block8/enable");
    $data_block_8['type'] = Mage::getStoreConfig("mobile_configuration/block8/type");
    $data_block_8['position'] = Mage::getStoreConfig("mobile_configuration/block8/position");
    $data_block_8['banner_type'] = Mage::getStoreConfig("mobile_configuration/block8/banner_type");
    $block_ads_id = Mage::getStoreConfig("mobile_configuration/block8/banner_ads");
    assign_banner_images($data_block_8, $block_ads_id);
    $dataMarge[] = $data_block_8;


//block 9
    $data_block_9['text'] = Mage::getStoreConfig("mobile_configuration/block9/text");
    $data_block_9['isActive'] = Mage::getStoreConfig("mobile_configuration/block9/enable");
    $data_block_9['type'] = Mage::getStoreConfig("mobile_configuration/block9/type");
    $data_block_9['position'] = Mage::getStoreConfig("mobile_configuration/block9/position");
    $category_id = Mage::getStoreConfig("mobile_configuration/block9/category");
    if ($category_id) {
        $data_block_9['category_id'] = $category_id;
        $data_block_9['products'] = getProductsForFeature($category_id);
    }
    $dataMarge[] = $data_block_9;


//block 10
    $data_block_10['text'] = Mage::getStoreConfig("mobile_configuration/block10/text");
    $data_block_10['isActive'] = Mage::getStoreConfig("mobile_configuration/block10/enable");
    $data_block_10['type'] = Mage::getStoreConfig("mobile_configuration/block10/type");
    $data_block_10['position'] = Mage::getStoreConfig("mobile_configuration/block10/position");
    $data_block_10['banner_type'] = Mage::getStoreConfig("mobile_configuration/block10/banner_type");
    $block_ads_id = Mage::getStoreConfig("mobile_configuration/block10/banner_ads");
    assign_banner_images($data_block_10, $block_ads_id);
    $dataMarge[] = $data_block_10;


//block 11
    $data_block_11['text'] = Mage::getStoreConfig("mobile_configuration/block11/text");
    $data_block_11['isActive'] = Mage::getStoreConfig("mobile_configuration/block11/enable");
    $data_block_11['type'] = Mage::getStoreConfig("mobile_configuration/block11/type");
    $data_block_11['position'] = Mage::getStoreConfig("mobile_configuration/block11/position");
    $data_block_11['banner_type'] = Mage::getStoreConfig("mobile_configuration/block11/banner_type");
    $block_ads_id = Mage::getStoreConfig("mobile_configuration/block11/banner_ads");
    assign_banner_images($data_block_11, $block_ads_id);
    $dataMarge[] = $data_block_11;


//block 12
    $data_block_12['text'] = Mage::getStoreConfig("mobile_configuration/block12/text");
    $data_block_12['isActive'] = Mage::getStoreConfig("mobile_configuration/block12/enable");
    $data_block_12['type'] = Mage::getStoreConfig("mobile_configuration/block12/type");
    $data_block_12['position'] = Mage::getStoreConfig("mobile_configuration/block12/position");
    $data_block_12['banner_type'] = Mage::getStoreConfig("mobile_configuration/block12/banner_type");
    $block_ads_id = Mage::getStoreConfig("mobile_configuration/block12/banner_ads");
    assign_banner_images($data_block_12, $block_ads_id);
    $dataMarge[] = $data_block_12;


//block 13
    $data_block_13['text'] = Mage::getStoreConfig("mobile_configuration/block13/text");
    $data_block_13['isActive'] = Mage::getStoreConfig("mobile_configuration/block13/enable");
    $data_block_13['type'] = Mage::getStoreConfig("mobile_configuration/block13/type");
    $data_block_13['position'] = Mage::getStoreConfig("mobile_configuration/block13/position");
    $brand_category_id = Mage::getStoreConfig("mobile_configuration/block13/brand");
    $data_block_13['list_brands'] = get_brand_data($brand_category_id);
    $dataMarge[] = $data_block_13;


//block 14
    $data_block_14['isActive'] = Mage::getStoreConfig("mobile_configuration/block14/enable");
    $data_block_14['type'] = Mage::getStoreConfig("mobile_configuration/block14/type");
    $data_block_14['position'] = Mage::getStoreConfig("mobile_configuration/block14/position");
    $data_block_14['banner_type'] = Mage::getStoreConfig("mobile_configuration/block14/banner_type");
    $block_ads_id = Mage::getStoreConfig("mobile_configuration/block14/banner_ads");
    assign_banner_images($data_block_14, $block_ads_id);
    $dataMarge[] = $data_block_14;

//block 15
    $data_block_15['text'] = Mage::getStoreConfig("mobile_configuration/block15/text");
    $data_block_15['isActive'] = Mage::getStoreConfig("mobile_configuration/block15/enable");
    $data_block_15['type'] = Mage::getStoreConfig("mobile_configuration/block15/type");
    $data_block_15['position'] = Mage::getStoreConfig("mobile_configuration/block15/position");
    $category_id = Mage::getStoreConfig("mobile_configuration/block15/category");
    if ($category_id) {
        $data_block_15['products'] = getProductsForFeature($category_id);
        $data_block_15['category_id'] = $category_id;
    }
    $dataMarge[] = $data_block_15;


//block 16
    $data_block_16['isActive'] = Mage::getStoreConfig("mobile_configuration/block15/enable");
    $data_block_16['type'] = Mage::getStoreConfig("mobile_configuration/block15/type");
    $data_block_16['position'] = Mage::getStoreConfig("mobile_configuration/block15/position");
    $data_block_16['banner_type'] = Mage::getStoreConfig("mobile_configuration/block15/banner_type");
    $block_ads_id = Mage::getStoreConfig("mobile_configuration/block16/banner_ads");
    assign_banner_images($data_block_16, $block_ads_id);
    $dataMarge[] = $data_block_16;


//block 17
    $data_block_17['text'] = Mage::getStoreConfig("mobile_configuration/block17/text");
    $data_block_17['isActive'] = Mage::getStoreConfig("mobile_configuration/block17/enable");
    $data_block_17['type'] = Mage::getStoreConfig("mobile_configuration/block17/type");
    $data_block_17['position'] = Mage::getStoreConfig("mobile_configuration/block17/position");
    $category_id = Mage::getStoreConfig("mobile_configuration/block17/category");
    if ($category_id) {
        $data_block_17['products'] = getProductsForFeature($category_id);
        $data_block_17['category_id'] = $category_id;
    }
    $dataMarge[] = $data_block_17;


//block 18
    $data_block_18['text'] = Mage::getStoreConfig("mobile_configuration/block18/text");
    $data_block_18['isActive'] = Mage::getStoreConfig("mobile_configuration/block18/enable");
    $data_block_18['type'] = Mage::getStoreConfig("mobile_configuration/block18/type");
    $data_block_18['position'] = Mage::getStoreConfig("mobile_configuration/block18/position");
    $category_id = Mage::getStoreConfig("mobile_configuration/block18/category");
    if ($category_id) {
        $data_block_18['category_id'] = $category_id;
        $data_block_18['products'] = getProductsForFeature($category_id);
        $category = Mage::getModel('catalog/category')->load($category_id);

        //$fromDate = '12/11/2018 4:00 PM';
        $fromDate = $category->getData('counting_downs');
        $timezone = Mage::getStoreConfig('general/locale/timezone');
        $fromDate = new DateTime($fromDate, new DateTimeZone($timezone));
        /* Converts to UTC/GMT time zone */
        $fromDate = $fromDate->format('U');
        /* Formats datetime in UTC/GMT timezone to string */
        //$fromDate = date("Y-m-d H:i:s",$fromDate);

        $data_block_18['countdownTime'] = $fromDate;
    }
    $dataMarge[] = $data_block_18;


//block 18
    $data_block_19['text'] = Mage::getStoreConfig("mobile_configuration/block19/text");
    $data_block_19['isActive'] = Mage::getStoreConfig("mobile_configuration/block19/enable");
    $data_block_19['type'] = Mage::getStoreConfig("mobile_configuration/block19/type");
    $data_block_19['position'] = Mage::getStoreConfig("mobile_configuration/block19/position");
    $category_id = Mage::getStoreConfig("mobile_configuration/block19/category");
    if ($category_id) {
        $data_block_19['category_id'] = $category_id;
        $data_block_19['products'] = getProductsForFeature($category_id);
        $category = Mage::getModel('catalog/category')->load($category_id);

        //$fromDate = '12/11/2018 4:00 PM';
        $fromDate = $category->getData('counting_downs');
        $timezone = Mage::getStoreConfig('general/locale/timezone');
        $fromDate = new DateTime($fromDate, new DateTimeZone($timezone));
        /* Converts to UTC/GMT time zone */
        $fromDate = $fromDate->format('U');
        /* Formats datetime in UTC/GMT timezone to string */
        //$fromDate = date("Y-m-d H:i:s",$fromDate);

        $data_block_19['countdownTime'] = $fromDate;
    }
    $dataMarge[] = $data_block_19;


    for ($i = 0; $i < sizeof($dataMarge); $i++) {
        for ($j = $i + 1; $j < sizeof($dataMarge); $j++) {
            if ($dataMarge[$i]['position'] > $dataMarge[$j]['position']) {
                $c = $dataMarge[$i];
                $dataMarge[$i] = $dataMarge[$j];
                $dataMarge[$j] = $c;
            }
        }
    }
    http_response_code(200);

    echo json_encode(array('status_code' => 200, 'message' => 'valid', 'postData' => $dataMarge));


    function assign_banner_images(&$data_block, $block_ads_id)
    {
        $imageModel = Mage::getModel('bannerads/images');
        $blockImage = Mage::getResourceModel('bannerads/bannerads')->lookupImagesId($block_ads_id);
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
                //array_push($data_block['banner_image'], $data);
                $data_block['banner_image'][] = $data;
            }
        }
    }

    function get_brand_data($category_id)
    {
        $data = array();
        $cat = Mage::getModel('catalog/category')->load($category_id);
        $subcats = $cat->getChildren();
        foreach (explode(',', $subcats) as $subCatid) {
            $_category = Mage::getModel('catalog/category')->load($subCatid);

            if ($_category->getIsActive()) {
                $item['id'] = $_category->getId();
                $item['name'] = $_category->getName();
                $item['url'] = $_category->getURL();
                if ($_category->getImageUrl()) {
                    $item['image'] = $_category->getImageUrl();
                }
                $data[] = $item;
            }
        }
        return $data;
    }
