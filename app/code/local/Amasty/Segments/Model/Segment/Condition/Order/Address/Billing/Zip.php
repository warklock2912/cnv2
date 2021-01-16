<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

    class Amasty_Segments_Model_Segment_Condition_Order_Address_Billing_Zip
    extends Amasty_Segments_Model_Condition_Abstract
    {
        public function __construct()
        {
            parent::__construct();
            $this->setType('amsegments/segment_condition_order_address_billing_zip');
            $this->setValue(null);
       }
 
       static function getDefaultLabel() {
            return 'Billing Zip';
       }
    
       public function asHtml()
       {
           return $this->getTypeElementHtml()
               . Mage::helper('amsegments')->__($this->getDefaultLabel() . ' %s %s:', $this->getOperatorElementHtml(), $this->getValueElementHtml())
               . $this->getRemoveLinkHtml();
       }
       
       protected function _prepareCollection(&$collection){
           
           return $collection
                   ->addOrderData("", " and salesOrder.state = 'complete'")
                   ->addOrderAddressData("billing");
           
       }
       
       protected function _getResultExpr(){
           $operator = $this->_getSqlOperator($this->getOperator());
           $value = $this->getValue();
           
           $sql = $this->getResource()->getReadConnection()->quoteInto("salesOrderAddress.postcode {$operator} ?", $value);
           
           return $this->getResource()->getReadConnection()->getCheckSql($sql, 1, 0);
        }
    }
?>