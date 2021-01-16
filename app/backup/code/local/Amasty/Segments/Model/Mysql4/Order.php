<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */ 
class Amasty_Segments_Model_Mysql4_Order extends Amasty_Segments_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('amsegments/order', 'entity_id');
    }
    
    protected function _getOrderSelect(){
        $adapter = $this->_getWriteAdapter();
        
        $salesOrder   = $this->getTable('sales/order');
        
        $select = $adapter->select()->from($salesOrder . ' as main_table', array(
            'customer.entity_id',
            'main_table.entity_id',
        ));
        
        return $select;
    }
    
    protected function _getCustomersOrderSelect(){
        $select = $this->_getOrderSelect();
        
        $select->joinLeft(
                array('customer' => $this->getTable('amsegments/customer')),
                'main_table.customer_id = customer.customer_id',
                array());
        
        $select->where('customer.customer_id IS NOT null');
        
        return $select;
        
    }
    
    protected function _getGuestsOrderSelect(){
        $select = $this->_getOrderSelect();
        
        $select->joinLeft(
                array('skipCustomer' => $this->getTable('amsegments/customer')),
                'main_table.customer_id = skipCustomer.customer_id',
                array());
        
        
        $select->joinInner(
                array('customer' => $this->getTable('amsegments/customer')),
                'main_table.customer_email = customer.customer_email',
                array());
        
        $select->where("skipCustomer.customer_id is null");
        return $select;
    }
    
    function bulkUpdate(){        
        $this->_guestsUpdate();
        $this->_customersUpdate();
    }
    
    protected function _guestsUpdate(){
        $adapter = $this->_getWriteAdapter();
        
        $select = $this->_getGuestsOrderSelect();
        
        $this->_addTimeRange($select, 'main_table.created_at');
        
        $sql = $select->insertFromSelect(array('e' => $this->getTable('amsegments/order')), array(
            "customer_id",
            "sales_order_id"
        ), FALSE);
        
        return $adapter->query($sql);
    }
    
    protected function _customersUpdate(){
        $adapter = $this->_getWriteAdapter();
        
        $select = $this->_getCustomersOrderSelect();
        
        $this->_addTimeRange($select, 'main_table.created_at');
        
        $sql = $select->insertFromSelect(array('e' => $this->getTable('amsegments/order')), array(
            "customer_id",
            "sales_order_id"
        ), FALSE);
//        var_dump($sql);
        return $adapter->query($sql);
    }
}
?>
