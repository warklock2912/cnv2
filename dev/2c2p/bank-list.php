<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();
$customer = getCustomer();
$customerID = $customer->getId();
$cards = Mage::getModel('p2c2p/token')->getCollection()->addFieldToFilter('user_id', $customerID);

$dataRes = convertCsvToArray(Mage::getStoreConfig('payment/p2c2p_onsite_internet_banking/channels', Mage::app()->getStore()));

dataResponse(200,'success',$dataRes);
