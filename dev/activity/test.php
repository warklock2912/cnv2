<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();
$customer = getCustomer();
$customerID = $customer->getId();
$arr = Mage::getModel('campaignmanage/raffleonline')->getCollection();

$dataRes = array();
if (count($arr)){
    foreach ($arr as $item){
        $data = array();
        $data['id'] = $item->getId();
        $data['customer_name'] = $item->getCustomerName();
        $data['store_id'] = $item->getStorepickupId();
        $data['shipping_id'] = $item->getShippingId();
        $data['cc_card_token'] = $item->getCcCardToken();
        $data['shipping_method'] = $item->getShippingMethod();
        $data['is_winner'] = $item->getIsWinner() == 1 ? true : false;
        $dataRes[] = $data;
    }
}
dataResponse(200,'success',$dataRes);
