<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();
if ( Mage::helper('cartreservation')->moduleEnabled() === true) {
    $timesArr = explode(',', Mage::getStoreConfig('cartreservation/checkout/time'));
    $revertTime = (int)$timesArr[0] * 86400 + (int)$timesArr[1] * 3600 + (int)$timesArr[2] * 60 + (int)$timesArr[3];
    dataResponse(200, 'valid', $revertTime);
} else {
    dataResponse(200, 'Cart Revert Not Enable',null);
}

