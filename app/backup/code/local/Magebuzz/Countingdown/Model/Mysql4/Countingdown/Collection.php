<?php
/*
* Copyright (c) 2013 www.magebuzz.com 
*/
class Magebuzz_Countingdown_Model_Mysql4_Countingdown_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	public function _construct() {
		parent::_construct();
		$this->_init('countingdown/countingdown');
	}
}