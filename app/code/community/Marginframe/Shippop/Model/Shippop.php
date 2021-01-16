<?php

class Marginframe_Shippop_Model_Shippop extends Mage_Core_Model_Abstract {
	
	public function _construct() {
		parent::_construct();
		$this->_init('shippop/shippop');
	}
}