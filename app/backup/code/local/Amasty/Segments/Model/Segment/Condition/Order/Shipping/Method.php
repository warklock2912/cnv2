<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

    class Amasty_Segments_Model_Segment_Condition_Order_Shipping_Method
    extends Amasty_Segments_Model_Condition_Abstract
    {
        protected $_inputType = 'select';
        
        public function __construct()
        {
            parent::__construct();
            $this->setType('amsegments/segment_condition_order_shipping_method');
            $this->setValue(null);
        }
        
        public function getValueSelectOptions()
        {
            $options = Mage::getModel('amsegments/shipping_allmethods')
                        ->toOptionArray();

            $this->setData('value_select_options', $options);
            
            return $options;
        }
                
        public function getValueElementType()
        {
            return 'select';
        }
        
        static function getDefaultLabel(){
            return 'One of used shipping methods';
        }
        
        public function asHtml()
        {
            return $this->getTypeElementHtml()
                . Mage::helper('amsegments')->__(self::getDefaultLabel() . ' %s %s:', $this->getOperatorElementHtml(), $this->getValueElementHtml())
                . $this->getRemoveLinkHtml();
        }
        
        protected function _prepareCollection(&$collection){

            return $collection
                    ->addOrderData("", " and salesOrder.state = 'complete'")
                    ->addPaymentData();

        }

        protected function _getResultExpr(){
            $adapter = $this->getResource()->getReadConnection();
            $operator = $this->_getSqlOperator($this->getOperator());
            
            $value = $this->getValue();
            $cond = $adapter->quoteInto("IFNULL(FIND_IN_SET(?, GROUP_CONCAT(salesOrder.shipping_method)), 0) $operator 0", $value);

            return $this->getResource()->getReadConnection()->getCheckSql($cond, 0, 1);
        }
    }
?>