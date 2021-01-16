<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

    class Amasty_Segments_Model_Segment_Condition_Order_Product_Attributes
    extends Amasty_Segments_Model_Condition_Product_Abstract
    {
        public function __construct()
        {
            parent::__construct();
            $this->setType('amsegments/segment_condition_order_product_attributes');
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
        
        function getAttributeSelect($websiteIds){
            $attributeSelect = $this->_getAttributeSelect($websiteIds);
            return $attributeSelect;
        }
        
        protected function _getAttributeSelect($websiteIds){
            $attribute = $this->getAttributeObject();
            $table = $attribute->getBackendTable();

            $resource = $this->getResource();
            $select = $resource->createSelect();
            $select->from(array('main'=>$table), array('entity_id'));

            if ($attribute->getAttributeCode() == 'category_ids') {
                $condition = $resource->createConditionSql(
                    'cat.category_id', $this->getOperatorForValidate(), $this->getValueParsed()
                );
                $categorySelect = $resource->createSelect();
                $categorySelect->from(array('cat'=>$resource->getTable('catalog/category_product')), 'product_id')
                    ->where($condition);
                $condition = 'main.entity_id IN ('.$categorySelect.')';
            } elseif ($attribute->isStatic()) {
                $condition = $this->getResource()->createConditionSql(
                    "main.{$attribute->getAttributeCode()}", $this->getOperator(), $this->getValue()
                );
            } else {
                $select->where('main.attribute_id = ?', $attribute->getId());
                $select->join(
                    array('store'=> $this->getResource()->getTable('core/store')),
                    'main.store_id=store.store_id',
                    array())
//                    ->where('store.website_id IN(?)', $websiteIds)
                ;
                $condition = $this->getResource()->createConditionSql(
                    'main.value', $this->getOperator(), $this->getValue()
                );
            }
            
            $select->where($condition);
            
            return $select;
        }
        
        public function index($websiteIds, $combineCondition){
            $adapter = $this->getResource()->getReadConnection();
            
            $attributeSelect = $this->_getAttributeSelect($websiteIds);
            
            $collection = Mage::getModel("amsegments/order")->getCollection()
                    ->addOrderItemData($websiteIds);
            
            $select = $collection->getSelect()
                    ->reset(Zend_Db_Select::COLUMNS)
                    ->columns(array(
                        new Zend_Db_Expr($adapter->quoteInto("? as segment_id", $this->getRule()->getId())),
                        "main_table.entity_id",
                        
                        new Zend_Db_Expr($adapter->quoteInto("? as level", $this->getId())),
                        new Zend_Db_Expr($adapter->quoteInto("? as parent", $combineCondition->getId()))
                        ));
            
            $select->where('salesItem.product_id in (' . $attributeSelect . ')');
            $select->group("main_table.entity_id");
            
            $sql = $select->insertFromSelect(array('e' => $this->getResource()->getTable('amsegments/product_index')), array(
                "segment_id",
                "order_id",
                "level",
                "parent"
            ), FALSE);

            return $this->getResource()->query($sql);
        }
    }
?>