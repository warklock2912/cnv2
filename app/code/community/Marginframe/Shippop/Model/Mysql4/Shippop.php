<?php

class Marginframe_Shippop_Model_Mysql4_Shippop extends Mage_Core_Model_Mysql4_Abstract {

	public function _construct() {    
		$this->_init('shippop/shippop', 'shippop_id');
	}
}