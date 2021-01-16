<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

    class Amasty_Segments_Model_Segment_Condition_Order_Combine_Period
    extends Amasty_Segments_Model_Condition_Abstract
    {
        protected $_inputType = 'numeric';
        
        public function __construct()
        {
            parent::__construct();
            $this->setType('amsegments/segment_condition_order_combine_period');
            $this->setValue(null);
        }
        
        static function getDefaultLabel() {
             return 'Was placed (days) ago';
        }
                
        public function getValueSelectOptions() 
        {
            $statuses = Mage::getModel('sales/order_status')->getResourceCollection()->toOptionArray();

            $this->setData('value_select_options', $statuses);
            
            return $statuses;
        }
        
        public function asHtml()
        {
            return $this->getTypeElementHtml()
                . Mage::helper('amsegments')->__(self::getDefaultLabel() . ' %s %s:', $this->getOperatorElementHtml(), $this->getValueElementHtml())
                . $this->getRemoveLinkHtml();
        }
        
        public function index($websiteIds, $combineCondition){
            $adapter = $this->getResource()->getReadConnection();
            
            $operator = $this->_getSqlOperator($this->getOperator());
            $value = $this->getValue();
            
            $collection = Mage::getModel("amsegments/order")->getCollection()
                    ->addOrderData();
            
            $select = $collection->getSelect()
                    ->reset(Zend_Db_Select::COLUMNS)
                    ->columns(array(
                        new Zend_Db_Expr($adapter->quoteInto("? as segment_id", $this->getRule()->getId())),
                        "main_table.entity_id",
                        
                        new Zend_Db_Expr($adapter->quoteInto("? as level", $this->getId())),
                        new Zend_Db_Expr($adapter->quoteInto("? as parent", $combineCondition->getId()))
                        ));
            
            $select->where("DATEDIFF(NOW(), salesOrder.created_at) " . $operator . " ? ", $value);
            
            $sql = $select->insertFromSelect(array('e' => $this->getResource()->getTable('amsegments/product_index')), array(
                "segment_id",
                "order_id",
                "level",
                "parent"
            ), FALSE);
            
            return $this->getResource()->query($sql);
        }
//        
//        public function getSubCondition(){
//            $adapter = $this->getResource()->getReadConnection();
//            
//            $operator = $this->_getSqlOperator($this->getOperator());
//            $value = $this->getValue();
//            
//            return $adapter->quoteInto("DATEDIFF(NOW(), salesOrder.created_at) " . $operator . " ? ", $value);
//        }
    }
?>