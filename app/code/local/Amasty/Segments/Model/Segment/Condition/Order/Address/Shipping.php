<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

    class Amasty_Segments_Model_Segment_Condition_Order_Address_Shipping
    extends Mage_Rule_Model_Condition_Product_Abstract
    {
        public function __construct()
        {
            parent::__construct();
            $this->setType('amsegments/segment_condition_order_address_shipping');
            $this->setValue(null);
        }
        
        public function getDefaultOperatorInputByType()
        {
            if (null === $this->_defaultOperatorInputByType) {
                parent::getDefaultOperatorInputByType();
                $this->_defaultOperatorInputByType['numeric'] = array('==', '!=', '>=', '>', '<=', '<');
                $this->_defaultOperatorInputByType['string'] = array('==', '!=', '{}', '!{}');
            }
            return $this->_defaultOperatorInputByType;
        }
        
        public function getNewChildSelectOptions()
        {
            $attributes = array(
                'city' => 'City',
                'region' => 'State',
                'country_id' => 'Country',
                'postcode' => 'Zip',
            );
            
            $conditions = array();
            
            foreach ($attributes as $code => $label) {
                $conditions[] = array('value'=> $this->getType() . '|' . $code, 'label'=>$label);
            }
            
            return array(
                'value' => $conditions,
                'label' => Mage::helper('amsegments')->__('Billing address')
            );
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