<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */ 
class Amasty_Followup_Model_Mysql4_History extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('amfollowup/history', 'history_id');
    }
    
}