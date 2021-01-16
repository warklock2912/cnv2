<?php

class Mage_P2c2p_Model_Mysql4_Token_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {    	
        parent::_construct();
        $this->_init('p2c2p/token');       
    }   
}
