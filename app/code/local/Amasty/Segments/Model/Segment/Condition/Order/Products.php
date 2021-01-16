<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

    class Amasty_Segments_Model_Segment_Condition_Order_Products
    extends Amasty_Segments_Model_Segment_Condition_Order_Combine
    {
        const NUMBER_PRODUCTS = 'number_products';
        protected $_productsLimited = false;
        
        public function __construct()
        {
            parent::__construct();
            $this->setType('amsegments/segment_condition_order_products');
        }
        
        public function loadAttributeOptions() 
        {
            $this->setAttributeOption(array(
                self::NUMBER_PRODUCTS => Mage::helper('amsegments')->__('Ordered products'),
            ));
            return $this;
        }
        
        protected function _getCollection($websiteIds){
            $collection = Mage::getModel("amsegments/customer")
                    ->getCollection()
//                    ->addOrderItemData()
                    ->filterByWebsite($websiteIds)
                    ->addProductIndexData($this->getId(), $this->getRule()->getId())
                    ->addOrderSalesItemData();
            
            $productsSql = array();
            
            foreach ($this->getConditions() as $condition) {
                if ($condition instanceof Amasty_Segments_Model_Segment_Condition_Order_Product_Attributes){
                    $productsSql[] = $condition->getAttributeSelect($websiteIds)->__toString();
                }
            }
            
            if (count($productsSql) > 0) {
            
                $collection->getSelect()->joinLeft( 
                    array('productsIds' => new Zend_Db_Expr("(" . implode(' UNION ', $productsSql) . ")")), 
                    'productsIds.entity_id = salesItem.product_id',
                    array()
                );

                $this->_productsLimited = true;
            }
            
            return $collection;
        }
        
        protected function _getResultExpr(){
            $resultExpr = parent::_getResultExpr();
            $operator = $this->_getSqlOperator($this->getOperator());
            $value = $this->getValue();
            
            $adapter = $this->getResource()->getReadConnection();
            
            switch($this->getAttributeElement()->getValue()){
                case self::NUMBER_PRODUCTS;

                    $expr = $this->_productsLimited ? "IF(productsIds.entity_id IS NULL, 0, 1)" : "1";

                    $resultExpr = $adapter->getCheckSql($adapter->quoteInto("SUM(" . $expr ." * salesItem.qty_ordered) $operator ?", $value), 1, 0);
                    break;
            }
            
            return $resultExpr;
        }
    }
?>