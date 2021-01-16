<?php
/*
* Copyright (c) 2013 www.magebuzz.com 
*/
class Tigren_Ruffle_Model_Status extends Varien_Object {
	const STATUS_ENABLED	= 1;
	const STATUS_DISABLED	= 2;

	static public function getOptionArray() {
		return array(
			self::STATUS_ENABLED    => Mage::helper('ruffle')->__('Enabled'),
			self::STATUS_DISABLED   => Mage::helper('ruffle')->__('Disabled')
		);
	}

    static public function getOptionDoc() {
        return array(
            '1'    => Mage::helper('ruffle')->__('Enabled'),
            '0'   => Mage::helper('ruffle')->__('Disabled')
        );
    }
}