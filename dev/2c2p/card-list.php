<?php
    require_once '../../app/Mage.php';
    require_once '../functions.php';
    checkIsLoggedIn();
    $customer = getCustomer();
    $customerID = $customer->getId();
    $cards = Mage::getModel('p2c2p/token')->getCollection()->addFieldToFilter('user_id', $customerID);

    $dataRes = array();
    if (count($cards)){
        foreach ($cards as $card){
            $data = array();
            $data['id'] = $card->getId();
            $data['card_token'] = $card->getStoredCardUniqueId();
            $data['card_number'] = $card->getMaskedPan();
            $data['expired_month'] = $card->getExpiredMonth();
            $data['expired_year'] = $card->getExpiredYear();
            $data['type'] = $card->getPaymentScheme();
            $data['is_default'] = $card->getIsDefault() == 1 ? true : false;
            $dataRes[] = $data;
        }
    }
    dataResponse(200,'success',$dataRes);
