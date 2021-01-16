<?php
/*
* Copyright (c) 2017 www.tigren.com 
*/
class Tigren_Ruffle_Model_Ruffle extends Mage_Core_Model_Abstract {
	const RUFFLE_GENERAL_GROUP_ID = 1;
    const RUFFLE_VIP_GROUP_ID = 4;
	public function _construct() {
		parent::_construct();
		$this->_init('ruffle/ruffle');
	}

	public function getRuffleByProductId($productId) {
        $this->_getResource()->getRuffleByProductId($this, $productId);
        return $this;
    }
}