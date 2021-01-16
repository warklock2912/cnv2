<?php

class Mage_P2c2predirect_Model_Token extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('p2c2p/token');
    }
}
