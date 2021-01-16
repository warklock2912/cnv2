<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

    class Amasty_Segments_Model_Segment_Condition_Customer_Subscriber
    extends Amasty_Segments_Model_Condition_Abstract
    {
        public function getValueElementType()
        {
            return 'select';
        }
        
        public function getValueSelectOptions() 
        {
            $hlr = Mage::helper("amsegments");
            
            $boolean = array(
                array(
                    'value' => 1,
                    'label' => $hlr->__("Yes")
                ),
                array(
                    'value' => 0,
                    'label' => $hlr->__("No")
                )
            );

            $this->setData('value_select_options', $boolean);
            
            return $boolean;
        }
        
        public function __construct()
        {
            parent::__construct();
            $this->setType('amsegments/segment_condition_customer_subscriber');
            $this->setValue(null);
       }
       
       static function getDefaultLabel() {
            return 'Is newsletter subscriber';
       }
    
       public function asHtml()
       {
           return $this->getTypeElementHtml()
               . Mage::helper('amsegments')->__($this->getDefaultLabel() . ' %s %s:', $this->getOperatorElementHtml(), $this->getValueElementHtml())
               . $this->getRemoveLinkHtml();
       }
       
       
       protected function _prepareCollection(&$collection){
           return $collection->addSubscriberData();
       }
       
       protected function _getResultExpr(){
           $operator = $this->_getSqlOperator($this->getOperator());
           $value = $this->getValue();
           
           $sql = $this->getResource()->getReadConnection()->quoteInto("(COUNT(subscriber.subscriber_id) > 0) {$operator} ?", $value);
           
           return $this->getResource()->getReadConnection()->getCheckSql($sql, 1, 0);
        }
    }
?>