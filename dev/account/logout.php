<?php

require_once '../../app/Mage.php';
require_once '../functions.php';
try {
    Mage::getSingleton("core/session", array("name" => "frontend"));
    $logged_in = Mage::getSingleton('customer/session')->isLoggedIn();
    if ($logged_in) {
        Mage::getSingleton('customer/session')->logout()->setBeforeAuthUrl(Mage::getUrl());
    }
} catch(Exception $e){

}

$userID = $_REQUEST['customer_id'] ? $_REQUEST['customer_id'] : '';
if(!empty($userID)){
    $user = Mage::getModel('pushnotification/device')
        ->getCollection()
        ->addFieldToFilter('user_id', $userID)->getFirstItem();
    $user->setData('user_id','')->save();
}
dataResponse(200, 'You\'re logged out');
