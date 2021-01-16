<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

    class Amasty_Segments_Model_Segment_Condition_Cart_Daysfrommodified
    extends Amasty_Segments_Model_Condition_Abstract
    {
        protected $_inputType = 'numeric';
        
        public function __construct()
        {
            parent::__construct();
            $this->setType('amsegments/segment_condition_cart_daysfrommodified');
            $this->setValue(null);
        }
        
       
       public function getNewChildSelectOptions()
       {
           return array('value' => $this->getType(),
               'label' => Mage::helper('amsegments')->__($this->getDefaultLabel()),
           );
       }
       
       static function getDefaultLabel() {
            return 'Days from cart modified';
       }
    
       public function asHtml()
       {
           return $this->getTypeElementHtml()
               . Mage::helper('amsegments')->__($this->getDefaultLabel() . ' %s %s:', $this->getOperatorElementHtml(), $this->getValueElementHtml())
               . $this->getRemoveLinkHtml();
       }
       
       protected function _prepareCollection(&$collection){
           
           return $collection
                   ->addCartData();
           
       }
       
       protected function _getResultExpr(){
           $operator = $this->_getSqlOperator($this->getOperator());
           $value = $this->getValue();
           
           $sql = $this->getResource()->getReadConnection()->quoteInto("FIND_IN_SET(1, GROUP_CONCAT(DATEDIFF(NOW(), salesQuote.updated_at)  {$operator} ?)) <> 0", $value);
           
           return $sql;
//           
//           return $this->getResource()->getReadConnection()->getCheckSql("DATEDIFF(NOW(), MAX(salesQuote.updated_at)) {$operator} {$value}", 1, 0);
        }
    }
?>