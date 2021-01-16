<?php
class Tigren_CardId_Model_CustomerCard extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('producteditrequest/producteditdetails');

    }
}