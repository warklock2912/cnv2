<?php
/**
 * @category    Bubble
 * @package     Bubble_DynamicCategory
 * @version     2.4.2
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_DynamicCategory_Model_Rule_Condition_Product_Type
    extends Bubble_DynamicCategory_Model_Rule_Condition_Product
{
    protected $_inputType = 'multiselect';

    protected function _construct()
    {
        parent::_construct();
        $this->setType('product_type');
        $this->setValueName('Product Type');
    }

    public function getValueElementType()
    {
        return 'multiselect';
    }

    public function getAttributeName()
    {
        return Mage::helper('dynamic_category')->__('Product Type');
    }

    public function getDefaultOperatorOptions()
    {
        return array(
            '()'  => Mage::helper('rule')->__('is one of'),
            '!()' => Mage::helper('rule')->__('is not one of')
        );
    }

    public function getValueSelectOptions()
    {
        return Mage_Catalog_Model_Product_Type::getOptions();
    }

    public function collectValidatedAttributes($productCollection)
    {
        return $this;
    }

    public function validate(Varien_Object $object)
    {
        return in_array($object->getTypeId(), $this->_getData('value'));
    }
}