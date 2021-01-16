<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

    class Amasty_Segments_Model_Segment_Condition_Product_Combine
    extends Mage_Rule_Model_Condition_Combine
    {
        public function __construct()
        {
            parent::__construct();
            $this->setType('amsegments/segment_condition_product_combine');
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
                '()'  => Mage::helper('amsegments')->__('is one of'),
                '!()' => Mage::helper('amsegments')->__('is not one of'),
            ));
            return $this;
        }

        public function getValueElementType() 
        {
            return 'text';
        }

        public function getNewChildSelectOptions() 
        {
            $result = array_merge_recursive(parent::getNewChildSelectOptions(),array(
                Mage::getModel('amsegments/segment_condition_product_attributes')->getNewChildSelectOptions()
            ));
            return $result;
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
        
        protected function _getResultExpr(){
            return "false";
        }
        
        protected function _getCollection($websiteIds){
            
            $collection = Mage::getModel("amsegments/customer")
                    ->getCollection()
                    ->filterByWebsite($websiteIds);
            
            return $collection;
        }
        
        public function process($websiteIds, $combineCondition = null){
            $adapter = $this->getResource()->getReadConnection();
            
            $collection = $this->_getCollection($websiteIds);
            
            $resultExpr = $this->_getResultExpr();
            
            $select = $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
                    ->columns(array(
                        new Zend_Db_Expr($adapter->quoteInto("? as segment_id", $this->getRule()->getId())),
                        new Zend_Db_Expr($adapter->quoteInto("? as level", $this->getId())),
                        new Zend_Db_Expr($adapter->quoteInto("? as parent", $combineCondition ? $combineCondition->getId() : null)),
                        "main_table.entity_id",
                        new Zend_Db_Expr($resultExpr)
                        ))
                    ->group("main_table.entity_id");
            
            $sql = $select->insertFromSelect(array('e' => $this->getResource()->getTable('amsegments/index')), array(
                "segment_id",
                "level",
                "parent",
                "customer_id",
                "result"
            ), FALSE);
            
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