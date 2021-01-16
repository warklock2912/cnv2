<?php

class Marginframe_Shippop_Model_Mysql4_Shippop_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	public function _construct() {
		parent::_construct();
		$this->_init('shippop/shippop');
	}
}