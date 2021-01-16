<?php
    require_once '../app/Mage.php';
    require_once 'functions.php';

    $collection = Mage::getModel('pushnotification/device')->getCollection()->setOrder("user_id","ASC");
    $data = array();
    foreach ($collection as $item) {
        $data[] = $item->getData();
    }

    dataResponse(200, "", $data);