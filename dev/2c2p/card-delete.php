<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();
$customer = getCustomer();
$customerID = $customer->getId();
$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);
if ($data['id']){
    $card = Mage::getModel('p2c2p/token')->load($data['id']);
        $card->delete();
        dataResponse(200,'Deleted.');
} else {
    dataResponse(400,'Invalid Request.');
}

