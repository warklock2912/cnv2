<?php
/*
* Copyright (c) 2013 www.magebuzz.com 
*/
class Magebuzz_Countingdown_Model_Mysql4_Countingdown extends Mage_Core_Model_Mysql4_Abstract {
	public function _construct() {    
		// Note that the countingdown_id refers to the key field in your database table.
		$this->_init('countingdown/countingdown', 'countingdown_id');
	}
}