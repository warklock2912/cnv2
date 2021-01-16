<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Model_Reports extends Mage_Core_Model_Abstract
{
    public function getRecords($reportType,$params)
    {
        return Mage::getSingleton('amreports_reports/'.$reportType)->getReport($params);
    }
}