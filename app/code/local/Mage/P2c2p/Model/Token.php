<?php

class Mage_P2c2p_Model_Token extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('p2c2p/token');
    }

    public function getByCardUniqueToken($cardToken){
        $this->getResource()->loadByCardUniqueToken($this, $cardToken);
        return $this;
    }
}
