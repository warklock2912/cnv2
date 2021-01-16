<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

/**
 * @author Amasty
 */ 
class Amasty_Segments_Model_Mysql4_Index_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amsegments/index');
    }
    
    function addResultSegmentData($segmentId){
        return $this->addResultSegmentsData(array($segmentId));
    }
    
    function addResultSegmentsData($ids = array()){
        $this->getSelect()->joinLeft( 
                array('customer' => $this->getTable('amsegments/customer')), 
                'customer.entity_id = main_table.customer_id',
                array("customer.*")
        );
        
        $this->addFieldToFilter('main_table.segment_id', array('in' => $ids));
        $this->addFieldToFilter('main_table.parent', array('eq' => ""));
        $this->addFieldToFilter('main_table.result', array('eq' => 1));
        
        return $this;
    }
}
?>