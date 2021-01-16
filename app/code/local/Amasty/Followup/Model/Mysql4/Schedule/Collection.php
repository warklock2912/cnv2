<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

/**
 * @author Amasty
 */ 
class Amasty_Followup_Model_Mysql4_Schedule_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amfollowup/schedule');
    }
    
    function addRule($rule){
        $this->addFilter('rule_id', $rule->getId());
//        $this->addFieldToFilter('delayed_start', array('gt' => 0));
        
        return $this;
    }
      
}