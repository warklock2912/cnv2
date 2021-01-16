<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

    class Amasty_Segments_Model_Segment_Condition_Order_Total
    extends Amasty_Segments_Model_Segment_Condition_Order_Combine
    {
        const AVERAGE_ORDER_VALUE = 'average_order_value';
        const TOTAL_ORDER_AMOUNT = 'total_orders_amount';
        const NUMBER_PRODUCTS = 'number_products';
        
        public function __construct()
        {
            parent::__construct();
            $this->setType('amsegments/segment_condition_order_total');
        }
        
        public function loadAttributeOptions() 
        {
            $this->setAttributeOption(array(
                self::TOTAL_ORDER_AMOUNT => Mage::helper('amsegments')->__('Total Sales Amount'),
                self::AVERAGE_ORDER_VALUE => Mage::helper('amsegments')->__('Average Order Value'),
            ));
            return $this;
        }
        
        protected function _getResultExpr(){
            $resultExpr = parent::_getResultExpr();
            $operator = $this->_getSqlOperator($this->getOperator());
            $value = $this->getValue();
            
            $adapter = $this->getResource()->getReadConnection();
            
            switch($this->getAttributeElement()->getValue()){
                case self::AVERAGE_ORDER_VALUE;
                    $resultExpr = $adapter->getCheckSql($adapter->quoteInto("AVG(salesOrder.grand_total) $operator ?", $value), 1, 0);
                    break;
                case self::TOTAL_ORDER_AMOUNT:
                    $resultExpr = $adapter->getCheckSql($adapter->quoteInto("SUM(salesOrder.grand_total) $operator ?", $value), 1, 0);
                    break;
                case self::NUMBER_PRODUCTS:
                    $resultExpr = $adapter->getCheckSql($adapter->quoteInto("COUNT(salesOrder.total_item_count) $operator ?", $value), 1, 0);
                    break;
            }
            
            return $resultExpr;
        }
    }
?>