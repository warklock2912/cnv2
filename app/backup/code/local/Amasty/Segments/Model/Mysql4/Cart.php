<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */ 
class Amasty_Segments_Model_Mysql4_Cart extends Amasty_Segments_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('amsegments/cart', 'entity_id');
    }
    
    protected function _getQuoteSelect(){
        $adapter = $this->_getWriteAdapter();
        
        $salesQuote   = $this->getTable('sales/quote');
        
        $select = $adapter->select()->from($salesQuote . ' as main_table', array(
            'customer.entity_id',
            'main_table.entity_id',
        ));
        
        return $select;
    }
    
    protected function _getCustomersQuoteSelect(){
        $select = $this->_getQuoteSelect();
        
        $select->joinLeft(
                array('customer' => $this->getTable('amsegments/customer')),
                'main_table.customer_id = customer.customer_id',
                array());
        
        $select->where('customer.customer_id IS NOT null');
        
        return $select;
        
    }
    
    protected function _getGuestsQuoteSelect(){
        $select = $this->_getQuoteSelect();
        
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
    
    function _clear(){
        $this->_getWriteAdapter()->delete(
            $this->getTable('amsegments/cart')
        );
        return $this;
    }
    
    function bulkUpdate(){
        $this->_clear();
        $this->_guestsUpdate();
        $this->_customersUpdate();
    }
    
    
    protected function _guestsUpdate(){
        $adapter = $this->_getWriteAdapter();
        
        $select = $this->_getGuestsQuoteSelect();
        
        $select->where("main_table.is_active = 1");
        
        $sql = $select->insertFromSelect(array('e' => $this->getTable('amsegments/cart')), array(
            "customer_id",
            "quote_id"
        ), FALSE);
        
        return $adapter->query($sql);
    }
    
    protected function _customersUpdate(){
        $adapter = $this->_getWriteAdapter();
        
        $select = $this->_getCustomersQuoteSelect();
        
        $select->where("main_table.is_active = 1");
        
        $sql = $select->insertFromSelect(array('e' => $this->getTable('amsegments/cart')), array(
            "customer_id",
            "quote_id"
        ), FALSE);
        
        return $adapter->query($sql);
    }
}