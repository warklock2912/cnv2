<?php
/**
 * @category    Bubble
 * @package     Bubble_DynamicCategory
 * @version     2.4.2
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_DynamicCategory_Model_Rule_Condition_Product_Parent
    extends Bubble_DynamicCategory_Model_Rule_Condition_Product
{
    protected $_inputType = 'select';

    protected function _construct()
    {
        parent::_construct();
        $this->setType('product_parent');
        $this->setValueName('Replace Matching Simple Products By Parent Products');
    }

    public function getValueElementType()
    {
        return 'select';
    }

    public function getAttributeName()
    {
        return Mage::helper('dynamic_category')->__('Replace Matching Simple Products By Parent Products');
    }

    public function getDefaultOperatorOptions()
    {
        return array('=='  => Mage::helper('dynamic_category')->__('and'));
    }

    public function getValueSelectOptions()
    {
        return array(
            array('value' => 'keep_orphans', 'label' => Mage::helper('dynamic_category')->__('Keep Orphans')),
            array('value' => 'remove_orphans', 'label' => Mage::helper('dynamic_category')->__('Discard Orphans')),
        );
    }

    public function collectValidatedAttributes($productCollection)
    {
        return $this;
    }

    public function validate(Varien_Object $object)
    {
        return true; // Fake condition, filtering will be processed in global product collection
    }
}