<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

    class Amasty_Segments_Model_Segment_Condition_Product_Attributes
    extends Amasty_Segments_Model_Condition_Product_Abstract
    {
        public function __construct()
        {
            parent::__construct();
            $this->setType('amsegments/segment_condition_product_attributes');
            $this->setValue(null);
        }
        
//        public function getDefaultOperatorInputByType()
//        {
//            if (null === $this->_defaultOperatorInputByType) {
//                parent::getDefaultOperatorInputByType();
//                $this->_defaultOperatorInputByType['numeric'] = array('==', '!=', '>=', '>', '<=', '<');
//                $this->_defaultOperatorInputByType['string'] = array('==', '!=', '{}', '!{}');
//            }
//            return $this->_defaultOperatorInputByType;
//        }
//        
        public function loadAttributeOptions()
        {
            $ret = parent::loadAttributeOptions();
            
            $attributes = $this->getAttributeOption();
            
            $unset = array(
                'activation_information', 'options_container',
                'finish', 'gender', 'in_depth', 'enable_googlecheckout',
                'page_layout', 'price_view'
            );
            
            foreach($unset as $attr){
                if (isset($attributes[$attr])){
                    unset($attributes[$attr]);
                }
            }
            
            $this->setAttributeOption($attributes);
            
            return $ret;
        }
        
        public function getNewChildSelectOptions()
        {
            $attributes = $this->loadAttributeOptions()->getAttributeOption();
            $conditions = array();
            foreach ($attributes as $code => $label) {
                $conditions[] = array('value'=> $this->getType() . '|' . $code, 'label'=>$label);
            }

            return array(
                'value' => $conditions,
                'label' => Mage::helper('amsegments')->__('Product Attributes')
            );
        }
        
        public function asHtml()
        {
            return Mage::helper('amsegments')->__('Product %s', parent::asHtml());
        }
        
        public function getAttributeObject()
        {
            return Mage::getSingleton('eav/config')->getAttribute('catalog_product', $this->getAttribute());
        }
        
        public function getResource()
        {
            return Mage::getResourceSingleton('amsegments/segment');
        }
        
        function joinAttribute($collection, $mainTableAlias){
            $ret = null;
            $attribute = $this->getAttributeObject();

            $table = $attribute->getBackendTable();
            $alias = 'cust_attr_' . $attribute->getAttributeCode();

            if ($attribute->getAttributeCode() == 'category_ids') {
                $join = $mainTableAlias . '.product_id = ' . $alias . '.entity_id';

                $field = $alias . '.entity_id';
                
                $resource = $this->getResource();
                
                $condition = $resource->createConditionSql(
                    'cat.category_id', $this->getOperatorForValidate(), $this->getValueParsed()
                );
                $categorySelect = $resource->createSelect();
                $categorySelect->from(array('cat'=>$resource->getTable('catalog/category_product')), 'product_id')
                    ->where($condition);

                $join .= ' and ' . $field . ' IN ('.$categorySelect.')';

                $collection->getSelect()->join(
                                                array($alias => $table),
                                                $join,
                                                array());
                
            } else {
                $joinLeft = $mainTableAlias . '.product_id = ' . $alias . '.entity_id';

                if ($attribute->isStatic()) {
                    $field = $alias . ".{$attribute->getAttributeCode()}";
                } else {
                    $joinLeft .= ' and ' . $alias . '.attribute_id = ' . $attribute->getId();
                    $field = $alias . '.value';
                }

                $collection->getSelect()->joinLeft(
                                    array($alias => $table),
                                    $joinLeft,
                                    array());
                $ret = $field;
            }
            

                    
            return $ret;
        }
    }
?>