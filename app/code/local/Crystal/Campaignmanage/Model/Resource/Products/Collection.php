<?php

class Crystal_Campaignmanage_Model_Resource_Products_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('campaignmanage/products');
    }
}