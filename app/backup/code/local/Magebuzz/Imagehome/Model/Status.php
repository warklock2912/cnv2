<?php
/*
* Copyright (c) 2013 www.magebuzz.com 
*/
class Magebuzz_Imagehome_Model_Status extends Varien_Object {
	const STATUS_ENABLED	= 1;
	const STATUS_DISABLED	= 2;

	static public function getOptionArray() {
		return array(
			self::STATUS_ENABLED    => Mage::helper('imagehome')->__('Enabled'),
			self::STATUS_DISABLED   => Mage::helper('imagehome')->__('Disabled')
		);
	}
}