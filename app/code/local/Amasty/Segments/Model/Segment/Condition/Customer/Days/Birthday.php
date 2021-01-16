<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

    class Amasty_Segments_Model_Segment_Condition_Customer_Days_Birthday
    extends Amasty_Segments_Model_Condition_Abstract
    {
        protected $_inputType = 'numeric';
        
        public function __construct()
        {
            parent::__construct();
            $this->setType('amsegments/segment_condition_days_registration');
            $this->setValue(null);
       }
       
       static function getDefaultLabel() {
            return 'Days before birthday';
       }
    
       public function asHtml()
       {
           return $this->getTypeElementHtml()
               . Mage::helper('amsegments')->__($this->getDefaultLabel() . ' %s %s:', $this->getOperatorElementHtml(), $this->getValueElementHtml())
               . $this->getRemoveLinkHtml();
       }
       
       
       protected function _prepareCollection(&$collection){
           
            $attribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'dob');

            $table = $attribute->getBackendTable();
            $alias = 'cust_attr_' . $attribute->getAttributeCode();

            $joinLeft = 'main_table.customer_id = ' . $alias . '.entity_id';
            if ($attribute->isStatic()) {
                $field = $alias . ".{$attribute->getAttributeCode()}";
            } else {
                $joinLeft .= ' and ' . $alias . '.attribute_id = ' . $attribute->getId();
                $field = $alias . '.value';
            }

            $collection->getSelect()->joinLeft(
                    array($alias => $table),
                    $joinLeft,//'main_table.customer_id = ' . $alias . '.entity_id',
                    array());
       }
       
       protected function _getResultExpr(){
           $operator = $this->_getSqlOperator($this->getOperator());
           $value = $this->getValue();
           
           $sql = $this->getResource()->getReadConnection()->quoteInto("DATEDIFF(STR_TO_DATE(CONCAT(DAY(cust_attr_dob.value), '-', MONTH(cust_attr_dob.value), '-', IF (STR_TO_DATE(CONCAT(DAY(cust_attr_dob.value), '-', MONTH(cust_attr_dob.value), '-', YEAR(CURDATE())), '%d-%m-%Y') >= CURDATE(), YEAR(CURDATE()), YEAR(CURDATE() + INTERVAL 1 YEAR))), '%d-%m-%Y'), CURDATE()) {$operator} ?", $value);
           
           return $this->getResource()->getReadConnection()->getCheckSql($sql, 1, 0);
        }
    }
?>