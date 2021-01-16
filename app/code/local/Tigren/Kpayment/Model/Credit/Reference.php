<?php

class Tigren_Kpayment_Model_Credit_Reference extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('kpayment/credit_reference');
    }

    public function loadByChargeId($chargeId)
    {
        $this->_getResource()->loadByChargeId($this, $chargeId);
        return $this;
    }
}
