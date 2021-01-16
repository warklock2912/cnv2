<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

/**
 * @author Amasty
 */ 
class Amasty_Segments_Model_Mysql4_Segment_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amsegments/segment');
    }
    
    function filterByStatus($status = Amasty_Segments_Model_Segment::STATUS_ACTIVE){
        $this->addFieldToFilter('main_table.is_active', array('eq' => $status));
        
        return $this;
    }

    public function toOptionArray()
    {
        return $this->_toOptionArray("segment_id", "name");
    }
    
    protected function _afterLoad()
    {
        $this->walk('afterLoad');
    }

}
?>