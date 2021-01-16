<?php

class Mage_P2c2p_Model_Mysql4_Meta extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    	
        $this->_init('p2c2p/meta', 'order_id');    
        $this->_isPkAutoIncrement = false;
    }
}
