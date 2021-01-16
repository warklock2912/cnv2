<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

/**
 * @author Amasty
 */ 
class Amasty_Segments_Model_Mysql4_Order_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amsegments/order');
    }
    
    function addOrderData(){
        
        $this->getSelect()->joinLeft( 
                array('salesOrder' => $this->getTable('sales/order')),
                'salesOrder.entity_id = main_table.sales_order_id',
                array()
        );
        
        return $this;
    }
    
    function addOrderItemData(){
        
        $this->getSelect()->joinLeft( 
                array('salesItem' => $this->getTable('sales/order_item')), 
                'salesItem.order_id = main_table.sales_order_id',
                array()
        );
        
        return $this;
    }
}
?>