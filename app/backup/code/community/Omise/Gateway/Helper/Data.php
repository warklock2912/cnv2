<?php
class Omise_Gateway_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getPublicKey(){
    	$publicKey = "";
    	$omise = Mage::getSingleton('omise_gateway/config')->load(1);
        $publicKey = $omise->public_key;

        // Replace keys with test keys if test mode was enabled.
        if ($omise->test_mode) {
            $publicKey = $omise->public_key_test;
        }
        return $publicKey;
    }
}