<?php
/*
* Copyright (c) 2017 www.tigren.com 
*/
class Tigren_Ruffle_Model_Mysql4_Joiner_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	public function _construct() {
		parent::_construct();
		$this->_init('ruffle/joiner');
	}
}