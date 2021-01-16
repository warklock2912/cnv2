<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

    class Amasty_Segments_Model_Segment_Condition_Order_Orders
    extends Amasty_Segments_Model_Segment_Condition_Order_Combine
    {
        const QTY_ORDERS = 'qty_orders';
        
        public function __construct()
        {
            parent::__construct();
            $this->setType('amsegments/segment_condition_order_orders');
        }
        

        public function loadAttributeOptions() 
        {
            $this->setAttributeOption(array(
                self::QTY_ORDERS => Mage::helper('amsegments')->__('Orders quantity'),
            ));
            return $this;
        }

        protected function _getResultExpr(){
            $resultExpr = parent::_getResultExpr();
            $operator = $this->_getSqlOperator($this->getOperator());
            $value = $this->getValue();
            
            $adapter = $this->getResource()->getReadConnection();
            
            switch($this->getAttributeElement()->getValue()){
                case self::QTY_ORDERS:
                    $resultExpr = $adapter->getCheckSql($adapter->quoteInto("COUNT(DISTINCT(salesOrder.entity_id)) $operator ?", $value), 1, 0);
                    break;
            }
            return $resultExpr;
        }
    }
?>