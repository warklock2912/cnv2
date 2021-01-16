<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

/**
 * @author Amasty
 */ 
class Amasty_Followup_Model_Mysql4_Rule_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amfollowup/rule');
    }
    
    function addStartFilter($types = array()){
        $this->addFilter('is_active', Amasty_Followup_Model_Rule::STATUS_ACTIVE);
        $this->addFieldToFilter('start_event_type', array('in' => $types));
        return $this;
    }
    
    function filterByIds($ids){
        $this->addFieldToFilter('main_table.rule_id', array('in' => $ids));
        return $this;
    }
      
}