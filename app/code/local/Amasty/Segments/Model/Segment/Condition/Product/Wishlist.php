<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

    class Amasty_Segments_Model_Segment_Condition_Product_Wishlist
    extends Amasty_Segments_Model_Segment_Condition_Product_Combine
    {
        const VIEWED = 'viewed';
        
        public function __construct()
        {
            parent::__construct();
            $this->setType('amsegments/segment_condition_product_wishlist');
        }
        

        public function loadAttributeOptions() 
        {
            $this->setAttributeOption(array(
                self::VIEWED => Mage::helper('amsegments')->__('Products in wishlist'),
            ));
            return $this;
        }
        
        protected function _getCollection($websiteIds){
            $collection = parent::_getCollection($websiteIds);
            $collection->addWishlistProductData();
            
            
            $conditions = array();
            
            foreach ($this->getConditions() as $condition) {
                $field = $condition->joinAttribute($collection, 'wishlistItem');

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
                    $resultExpr = $adapter->getCheckSql($adapter->quoteInto("COUNT(wishlistItem.product_id) $operator ?", $value), 1, 0);
                    break;
            }
            return $resultExpr;
        }
    }
?>