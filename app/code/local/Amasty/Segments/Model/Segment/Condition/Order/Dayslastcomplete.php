<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

    class Amasty_Segments_Model_Segment_Condition_Order_Dayslastcomplete
    extends Amasty_Segments_Model_Condition_Abstract
    {
        protected $_inputType = 'numeric';
        
        public function __construct()
        {
            parent::__construct();
            $this->setType('amsegments/segment_condition_order_dayslastcomplete');
            $this->setValue(null);
        }
        
       
       public function getNewChildSelectOptions()
       {
           return array('value' => $this->getType(),
               'label' => Mage::helper('amsegments')->__($this->getDefaultLabel()),
           );
       }
       
       static function getDefaultLabel() {
            return 'Days from last completed order';
       }
    
       public function asHtml()
       {
           return $this->getTypeElementHtml()
               . Mage::helper('amsegments')->__($this->getDefaultLabel() . ' %s %s:', $this->getOperatorElementHtml(), $this->getValueElementHtml())
               . $this->getRemoveLinkHtml();
       }
       
       protected function _prepareCollection(&$collection){
           
           return $collection
                   ->addOrderData("", " and salesOrder.state = 'complete'");
           
       }
       
       protected function _getResultExpr(){
           $operator = $this->_getSqlOperator($this->getOperator());
           $value = $this->getValue();
           
           $sql = $this->getResource()->getReadConnection()->quoteInto("DATEDIFF(NOW(), MAX(salesOrder.created_at)) {$operator} ?", $value);
           
           return $this->getResource()->getReadConnection()->getCheckSql($sql, 1, 0);
        }
    }
?>