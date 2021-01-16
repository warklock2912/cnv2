<?php

class Mage_P2c2predirect_Model_Mysql4_Token extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('p2c2predirect/token', 'p2c2predirect_id');
    }
}
