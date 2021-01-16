<?php

class Mage_P2c2p_Model_Meta extends Mage_Core_Model_Abstract
{
	public function _construct()
	{		
		parent::_construct();
		$this->_init('p2c2p/meta');
	}
}
