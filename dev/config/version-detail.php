<?php
require_once '../../app/Mage.php';
require_once '../functions.php';


$iosData = array(
    'url' => Mage::getStoreConfig('appupdate_options/Ios/url'),
    'lastest' => Mage::getStoreConfig('appupdate_options/Ios/lastest'),
    'minimum' => Mage::getStoreConfig('appupdate_options/Ios/minimum'),
    'enable' => Mage::getStoreConfig('appupdate_options/Ios/status') == 1 ? true : false
);
$androidData = array(
    'url' => Mage::getStoreConfig('appupdate_options/Android/url'),
    'lastest' => Mage::getStoreConfig('appupdate_options/Android/lastest'),
    'minimum' => Mage::getStoreConfig('appupdate_options/Android/minimum'),
    'enable' => Mage::getStoreConfig('appupdate_options/Android/status') == 1 ? true : false
);
$dataArr = array(
    'ios' => $iosData,
    'android' => $androidData
);
dataResponse(200, 'valid', $dataArr);