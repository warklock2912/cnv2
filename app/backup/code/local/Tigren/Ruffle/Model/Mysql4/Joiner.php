<?php
class Tigren_Ruffle_Model_Mysql4_Joiner extends Mage_Core_Model_Mysql4_Abstract {
	public function _construct() {    
		$this->_init('ruffle/joiner', 'joiner_id');
	}
}