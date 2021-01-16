<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

    class Amasty_Segments_Model_Segment_Condition_Order_Combine
    extends Mage_Rule_Model_Condition_Combine
    {
        public function __construct()
        {
            parent::__construct();
            $this->setType('amsegments/segment_condition_order_combine');
        }
        
        public function loadArray($arr, $key = 'conditions') 
        {
            $this->setAttribute($arr['attribute']);
            $this->setOperator($arr['operator']);
            parent::loadArray($arr, $key);
            return $this;
        }

        public function asXml($containerKey = 'conditions', $itemKey = 'condition') 
        {
            $xml = '<attribute>' . $this->getAttribute() . '</attribute>'
                    . '<operator>' . $this->getOperator() . '</operator>'
                    . parent::asXml($containerKey, $itemKey);
            return $xml;
        }    

        public function loadValueOptions() 
        {
            return $this;
        }

        public function loadOperatorOptions() {
            $this->setOperatorOption(array(
                '=='  => Mage::helper('amsegments')->__('is'),
                '!='  => Mage::helper('amsegments')->__('is not'),
                '>='  => Mage::helper('amsegments')->__('equals or greater than'),
                '<='  => Mage::helper('amsegments')->__('equals or less than'),
                '>'   => Mage::helper('amsegments')->__('greater than'),
                '<'   => Mage::helper('amsegments')->__('less than'),
//                '()'  => Mage::helper('amsegments')->__('is one of'),
//                '!()' => Mage::helper('amsegments')->__('is not one of'),
            ));
            return $this;
        }

        public function getValueElementType() 
        {
            return 'text';
        }

        public function getNewChildSelectOptions() 
        {
            $conditions = array(
                array('label' => Mage::helper('amsegments')->__('Please choose condition'), 'value' => ''),
                array('label' => Mage::helper('amsegments')->__('Order Status'), 'value' => 'amsegments/segment_condition_order_combine_status'),
                array('label' => Mage::helper('amsegments')->__('Was placed (days) ago'), 'value' => 'amsegments/segment_condition_order_combine_period'),
                array('label' => Mage::helper('amsegments')->__('Placed'), 'value' => 'amsegments/segment_condition_order_combine_placed'),
                Mage::getModel('amsegments/segment_condition_order_product_attributes')->getNewChildSelectOptions(),
                
            );
            return $conditions;
        }

        public function asHtml() 
        {
            $html = $this->getTypeElement()->getHtml() .
                    Mage::helper('amsegments')->__(' If %s %s %s for a subselection of orders matching %s of these conditions:', $this->getAttributeElement()->getHtml(), $this->getOperatorElement()->getHtml(), $this->getValueElement()->getHtml(), $this->getAggregatorElement()->getHtml());

            if ($this->getId() != '1') {
                $html .= $this->getRemoveLinkHtml();
            }
            return $html;
        }
        
        protected function _joinProductIndex($select, $conditonsCount)
        {
            $select->joinInner(
                array('product_index' => $this->getResource()->getTable('amsegments/product_index')), 
                'product_index.order_id = order.entity_id',
                array()
            );
            
            $select->where("product_index.parent = ?", $this->getId());
            $select->where("product_index.segment_id = ?", $this->getRule()->getId());
            $select->having("count(product_index.entity_id) >= ".intval($this->getAggregator() == 'all' ? $conditonsCount : 0));
        }
        
        public function index($websiteIds, $conditonsCount, $combineCondition = null){
            $adapter = $this->getResource()->getReadConnection();

            $collection = Mage::getModel("amsegments/customer")
                    ->getCollection();
            
            $select = $collection->getSelect()
                    ->joinInner(
                        array('order' => $this->getResource()->getTable('amsegments/order')), 
                        'order.customer_id = main_table.entity_id',
                        array()
                    )
                    ->reset(Zend_Db_Select::COLUMNS)
                    ->columns(array(
                        new Zend_Db_Expr($adapter->quoteInto("? as segment_id", $this->getRule()->getId())),
                        "order.entity_id",
                        
                        new Zend_Db_Expr($adapter->quoteInto("? as level", $this->getId())),
                        new Zend_Db_Expr($adapter->quoteInto("? as parent", $combineCondition->getId()))
                        ))
                    ->where("main_table.website_id IN (?)", $websiteIds);
            
            $select->group("order.entity_id");
            
            if ($conditonsCount > 0){
                $this->_joinProductIndex($select, $conditonsCount);
            }
            
            $sql = $select->insertFromSelect(array('e' => $this->getResource()->getTable('amsegments/product_index')), array(
                "segment_id",
                "order_id",
                "level",
                "parent"
            ), FALSE);
//            echo $sql;
//            exit(1);
            return $this->getResource()->query($sql);
        }
        
        protected function _getResultExpr(){
            return "false";
        }
        
        protected function _getCollection($websiteIds){
            
            $collection = Mage::getModel("amsegments/customer")
                    ->getCollection()
                    ->filterByWebsite($websiteIds)
                    ->addProductIndexData($this->getId(), $this->getRule()->getId());
            
            return $collection;
        }
        
        public function process($websiteIds, $combineCondition = null){
            
            $adapter = $this->getResource()->getReadConnection();
            
            $conditonsCount = 0;
            foreach ($this->getConditions() as $condition) {
                $condition->index($websiteIds, $this);
                $conditonsCount++;
            }
            
            $this->index($websiteIds, $conditonsCount, $combineCondition);
            
            $collection = $this->_getCollection($websiteIds);
            
            $resultExpr = $this->_getResultExpr();
            
            $select = $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
                    ->columns(array(
                        new Zend_Db_Expr($adapter->quoteInto("? as segment_id", $this->getRule()->getId())),
                        new Zend_Db_Expr($adapter->quoteInto("? as level", $this->getId())),
                        new Zend_Db_Expr($adapter->quoteInto("? as parent", $combineCondition ? $combineCondition->getId() : null)),
                        "main_table.entity_id",
                        $resultExpr
                        ))
                    ->group("main_table.entity_id");
            
            $sql = $select->insertFromSelect(array('e' => $this->getResource()->getTable('amsegments/index')), array(
                "segment_id",
                "level",
                "parent",
                "customer_id",
                "result"
            ), FALSE);
            
//            echo $sql;
//            exit;
            
            return $this->getResource()->query($sql);
        }
        
        protected function _getSqlOperator($operator)
        {
            return Mage::helper('amsegments')
                    ->getSqlOperator($operator);
        }
        
        public function getResource()
        {
            return Mage::getResourceSingleton('amsegments/segment');
        }
    }
?>