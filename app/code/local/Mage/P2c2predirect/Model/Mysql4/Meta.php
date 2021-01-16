<?php

class Mage_P2c2predirect_Model_Mysql4_Meta extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('p2c2predirect/meta', 'order_id');
        $this->_isPkAutoIncrement = false;
    }
}
