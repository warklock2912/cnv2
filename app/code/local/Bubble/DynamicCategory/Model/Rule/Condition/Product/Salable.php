<?php
/**
 * @category    Bubble
 * @package     Bubble_DynamicCategory
 * @version     2.4.2
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_DynamicCategory_Model_Rule_Condition_Product_Salable
    extends Bubble_DynamicCategory_Model_Rule_Condition_Product_Boolean
{
    protected function _construct()
    {
        parent::_construct();
        $this->setType('product_is_salable');
        $this->setValueName('Is Salable');
    }

    public function getAttributeName()
    {
        return Mage::helper('dynamic_category')->__('Is Salable');
    }

    public function getDefaultOperatorOptions()
    {
        return array('=='  => Mage::helper('rule')->__('is'));
    }

    public function collectValidatedAttributes($productCollection)
    {
        /** @var $adapter Varien_Db_Adapter_Pdo_Mysql */
        $adapter = Mage::getResourceSingleton('core/resource')->getReadConnection();
        $select = $adapter->select()
            ->from(Mage::getResourceSingleton('cataloginventory/stock_status')->getMainTable(), 'product_id')
            ->where('stock_status = 1');

        $this->_entityAttributeValues = array_flip($adapter->fetchCol($select));

        return $this;
    }

    public function validate(Varien_Object $object)
    {
        $isSalable = isset($this->_entityAttributeValues[$object->_getData('entity_id')]);

        return $this->getValue() ? $isSalable : !$isSalable;
    }
}