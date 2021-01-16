<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

    class Amasty_Segments_Model_Condition_Abstract
    extends Mage_Rule_Model_Condition_Abstract
    {
        public function getDefaultOperatorInputByType()
        {
            if (null === $this->_defaultOperatorInputByType) {
                parent::getDefaultOperatorInputByType();
                $this->_defaultOperatorInputByType['numeric'] = array('==', '!=', '>=', '>', '<=', '<');
                $this->_defaultOperatorInputByType['string'] = array('==', '!=', '{}', '!{}');
            }
            return $this->_defaultOperatorInputByType;
        }

        protected function _getCollection($websiteIds){
            $collection = Mage::getModel("amsegments/customer")
                    ->getCollection()
                    ->filterByWebsite($websiteIds);
            
            $this->_prepareCollection($collection);
            
            return $collection;
        }
        
        protected function _prepareCollection(&$collection){}
        
        protected function _getSelect($websiteIds, $combineCondition)
        {
            
            $adapter = $this->getResource()->getReadConnection();
            
            $collection = $this->_getCollection($websiteIds);
            
            $select = $collection->getSelect()
                    ->reset(Zend_Db_Select::COLUMNS)
                    ->columns(array(
                        new Zend_Db_Expr($adapter->quoteInto("? as segment_id", $this->getRule()->getId())),
                        new Zend_Db_Expr($adapter->quoteInto("? as level", $this->getId())),
                        new Zend_Db_Expr($adapter->quoteInto("? as parent", $combineCondition->getId())),
                        "main_table.entity_id",
                        $this->_getResultExpr()
                        ))
                    ->group("main_table.entity_id");
            
            return $select;
        }
        
        protected function _prepareSelect(&$select){}
        
        protected function _getResultExpr(){
            return new Zend_Db_Expr("0 as result");
        }
        
        function process($websiteIds, $combineCondition){
            
            $select = $this->_getSelect($websiteIds, $combineCondition);
            
            $this->_prepareSelect($select);
            
            $sql = $select->insertFromSelect(array('e' => $this->getResource()->getTable('amsegments/index')), array(
                "segment_id",
                "level",
                "parent",
                "customer_id",
                "result"
            ), FALSE);
//echo $sql."\n\n";
//exit;
            return $this->getResource()->query($sql);
        }
        
        public function getResource()
        {
            return Mage::getResourceSingleton('amsegments/segment');
        }
        
        protected function _getSqlOperator($operator)
        {
            return Mage::helper('amsegments')
                    ->getSqlOperator($operator);
        }
        
//        public function asHtml()
//        {
//            return $this->getTypeElementHtml()
//                . Mage::helper('amsegments')->__($this->getDefaultLabel() . ' %s %s:', $this->getOperatorElementHtml(), $this->getValueElementHtml())
//                . $this->getRemoveLinkHtml();
//        }
    }
?>