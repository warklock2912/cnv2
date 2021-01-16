<?php
/*
* Copyright (c) 2017 www.tigren.com 
*/
class Tigren_Ruffle_Model_Joiner extends Mage_Core_Model_Abstract {
	public function _construct() {
		parent::_construct();
		$this->_init('ruffle/joiner');
	}
}