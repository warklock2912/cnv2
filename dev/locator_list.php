<?php
require_once '../app/Mage.php';
require_once 'functions.php';

if (isset($_REQUEST['id'])) {
    $raffleId = $_REQUEST['id'];
    $raffle = Mage::getModel('campaignmanage/campaignonline')->load($raffleId);
    if ($raffle->getId()) {
        $data = array();
        $dataRes = array();

        if ($raffle->getStoresActive() != null) {
            $locatorList = explode(',', $raffle->getStoresActive());
            foreach ($locatorList as $locatorId) {
                $item = Mage::getModel('storepickup/store')->load($locatorId);

                $data['id'] = $item->getId();
                $data['title'] = $item->getStoreName();
                $dataRes[] = $data;
            }
        }
        dataResponse(200, 'Valid', $dataRes);
    } else {
        dataResponse(400, 'Invalid raffle id');
    }
} else {
    dataResponse(400, 'Missing raffle id');
}
