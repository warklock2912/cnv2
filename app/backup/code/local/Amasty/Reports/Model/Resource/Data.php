<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Model_Resource_Data extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('amreports/data', 'id');
    }
}