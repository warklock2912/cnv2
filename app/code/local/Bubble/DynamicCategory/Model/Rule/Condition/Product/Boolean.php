<?php
/**
 * @category    Bubble
 * @package     Bubble_DynamicCategory
 * @version     2.4.2
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
abstract class Bubble_DynamicCategory_Model_Rule_Condition_Product_Boolean
    extends Bubble_DynamicCategory_Model_Rule_Condition_Product
{
    protected $_inputType = 'boolean';

    public function getValueElementType()
    {
        return 'select';
    }

    public function getValue()
    {
        $value = parent::getValue();

        return null !== $value ? $value : 1;
    }

    public function getValueSelectOptions()
    {
        return array(
            array('value' => 1, 'label' => Mage::helper('adminhtml')->__('Yes')),
            array('value' => 0, 'label' => Mage::helper('adminhtml')->__('No')),
        );
    }

    public function validate(Varien_Object $object)
    {
        if (isset($this->_entityAttributeValues[$object->_getData('entity_id')])) {
            $object->setData($this->_getData('attribute'), $this->_entityAttributeValues[$object->_getData('entity_id')]);
        }

        return (bool) $this->_validateProduct($object);
    }
}
