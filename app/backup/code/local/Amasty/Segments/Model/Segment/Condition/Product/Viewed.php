<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

    class Amasty_Segments_Model_Segment_Condition_Product_Viewed
    extends Amasty_Segments_Model_Segment_Condition_Product_Combine
    {
        const VIEWED = 'viewed';
        
        public function __construct()
        {
            parent::__construct();
            $this->setType('amsegments/segment_condition_product_viewed');
        }
        

        public function loadAttributeOptions() 
        {
            $this->setAttributeOption(array(
                self::VIEWED => Mage::helper('amsegments')->__('Product viewed'),
            ));
            return $this;
        }
        
        protected function _getCollection($websiteIds){
            $collection = parent::_getCollection($websiteIds);
            $collection->addReportViewedProductData();
            $conditions = array();
            
            foreach ($this->getConditions() as $condition) {
                $field = $condition->joinAttribute($collection, 'report');

                if ($field) {
                    $conditions[] = $this->getResource()->createConditionSql(
                        $field, $condition->getOperator(), $condition->getValue()
                    );
                }

            }
            
            if (count($conditions) > 0)
                $collection->getSelect()->where(implode($this->getAggregator() == 'all' ?  " AND " : " OR ", $conditions));
            return $collection;
            
        }

        protected function _getResultExpr(){
            $resultExpr = parent::_getResultExpr();
            $operator = $this->_getSqlOperator($this->getOperator());
            $value = $this->getValue();
            
            $adapter = $this->getResource()->getReadConnection();
            
            switch($this->getAttributeElement()->getValue()){
                case self::VIEWED:
                    $resultExpr = $adapter->getCheckSql($adapter->quoteInto("COUNT(report.product_id) $operator ?", $value), 1, 0);
                    break;
            }
            return $resultExpr;
        }
    }
?>