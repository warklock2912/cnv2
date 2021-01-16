<?php
/*
* Copyright (c) 2013 www.magebuzz.com 
*/
class Magebuzz_Imagehome_Model_Mysql4_Imagehome extends Mage_Core_Model_Mysql4_Abstract {
	public function _construct() {    
		// Note that the imagehome_id refers to the key field in your database table.
		$this->_init('imagehome/imagehome', 'imagehome_id');
	}
}