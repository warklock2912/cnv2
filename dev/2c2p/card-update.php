<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();
$customer = getCustomer();
$customerID = $customer->getId();
$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);

if ($data['id']){
    $cardId = $data['id'];
    $customerCards = Mage::getModel('p2c2p/token')
        ->getCollection()
        ->addFieldToFilter('user_id', $customerID);

    foreach ($customerCards as $card):
        $card->setIsDefault(false)->save();
    endforeach;

    $card = Mage::getModel('p2c2p/token')->load($data['id']);
    $card->setIsDefault(true)->save();
    dataResponse(200,'Updated.');
} else {
    dataResponse(400,'Invalid Request.');
}

