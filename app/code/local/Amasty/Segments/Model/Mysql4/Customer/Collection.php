<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

/**
 * @author Amasty
 */ 
class Amasty_Segments_Model_Mysql4_Customer_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amsegments/customer');
    }
    
    function filterByWebsite($ids){
        $this->addFieldToFilter('main_table.website_id', array('in' => $ids));
        return $this;
    }
    
    function addOrderData($salesCondition = "", $salesOrderCondition = ""){
        $this->getSelect()->joinLeft( 
                array('order' => $this->getTable('amsegments/order')), 
                'order.customer_id = main_table.entity_id ' . $salesCondition,
                array()
        );
        
        $this->getSelect()->joinLeft( 
                array('salesOrder' => $this->getTable('sales/order')), 
                'salesOrder.entity_id = order.sales_order_id ' . $salesOrderCondition,
                array()
        );
        
        return $this;
    }
    
    function addOrderAddressData($addressType = "billing"){
        
        $this->getSelect()->joinLeft( 
                array('salesOrderAddress' => $this->getTable('sales/order_address')),
                'salesOrderAddress.parent_id = salesOrder.entity_id and salesOrderAddress.address_type = "'.$addressType.'"',
                array()
        );
        
        return $this;
    }
    
    function addPaymentData(){
        
        $this->getSelect()->joinLeft( 
                array('salesPayment' => $this->getTable('sales/order_payment')), 
                'salesPayment.parent_id = salesOrder.entity_id',
                array()
        );
        
        return $this;
    }
    
    function addStateFilter($state){
        $this->addFieldToFilter('salesOrder.state', array('eq' => $state));
        return $this;
    }
    
    function addIndexData($segmentId, $parent){
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        
        $extraCond = ' and ' . $adapter->quoteInto("index.parent = ? ",  $parent);
        $extraCond .= ' and ' . $adapter->quoteInto("index.segment_id = ?",  $segmentId);
        
        $this->getSelect()->joinLeft( 
                array('index' => $this->getTable('amsegments/index')), 
                'index.customer_id = main_table.entity_id '. $extraCond,
                array()
        );
        
        return $this;
    }
    
    function addCartData(){
        $this->getSelect()->joinLeft( 
                array('cart' => $this->getTable('amsegments/cart')), 
                'cart.customer_id = main_table.entity_id',
                array()
        );
        
        $this->getSelect()->joinLeft( 
                array('salesQuote' => $this->getTable('sales/quote')), 
                'salesQuote.entity_id = cart.quote_id',
                array()
        );
        
        return $this;
    }
    
    function addOrderItemData($salesCondition = "", 
            $salesOrderCondition = "", $orderItemCondition = ""){
        
        $this->addOrderData($salesCondition, $salesOrderCondition);
        
        $this->getSelect()->joinLeft( 
                array('salesItem' => $this->getTable('sales/order_item')), 
                'salesItem.order_id = salesOrder.entity_id ' . $orderItemCondition,
                array()
        );
        
        return $this;
    }
    
    function addProductIndexData($level, $segmentId){
        
        $this->getSelect()->joinLeft( 
                array('order' => $this->getTable('amsegments/order')), 
                'order.customer_id = main_table.entity_id ',
                array()
        );
        
        $this->getSelect()->joinLeft( 
                array('productIndex' => $this->getTable('amsegments/product_index')), 
                'productIndex.order_id = order.entity_id and productIndex.level = "'.$level.'" and productIndex.segment_id = "'.$segmentId.'"',
                array()
        );
        
        $this->getSelect()->joinLeft( 
                array('salesOrder' => $this->getTable('sales/order')), 
                'salesOrder.entity_id = IF(productIndex.entity_id IS NOT NULL, order.sales_order_id, NULL)',
                array()
        );
//        $this->addFieldToFilter('productIndex.level', array('eq' => $level));
//        $this->addFieldToFilter('productIndex.segment_id', array('eq' => $segmentId));


        return $this;
    }

    function addOrderSalesItemData(){
        $this->getSelect()->joinLeft(
                array('salesItem' => $this->getTable('sales/order_item')),
                'salesItem.order_id = salesOrder.entity_id ',
                array()
        );
        return $this;
    }
    
    function addSubscriberData(){
        $this->getSelect()->joinLeft( 
                array('subscriber' => $this->getTable('newsletter/subscriber')), 
                'main_table.customer_id = subscriber.customer_id and subscriber.subscriber_status = ' . Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED,
                array()
        );
        return $this;
    }
    
    function addLogData(){
        $this->getSelect()->joinLeft( 
                array('log' => $this->getTable('log/customer')), 
                'main_table.customer_id = log.customer_id',
                array()
        );
        return $this;
    }
    
    function addCustomerData(){
        $this->getSelect()->joinLeft( 
                array('customer' => $this->getTable('customer/entity')), 
                'main_table.customer_id = customer.entity_id',
                array()
        );
        return $this;
    }
    
    function addReportViewedProductData(){
        $this->getSelect()->joinLeft( 
                array('report' => $this->getTable('reports/viewed_product_index')), 
                'main_table.customer_id = report.customer_id',
                array()
        );
        return $this;
    }
    
    function addWishlistProductData(){
        $this->getSelect()->joinLeft( 
                array('wishlist' => $this->getTable('wishlist/wishlist')), 
                'main_table.customer_id = wishlist.customer_id',
                array()
        );
        $this->getSelect()->joinLeft( 
                array('wishlistItem' => $this->getTable('wishlist/item')), 
                'wishlistItem.wishlist_id = wishlist.wishlist_id',
                array()
        );
        
        return $this;
    }
}
?>