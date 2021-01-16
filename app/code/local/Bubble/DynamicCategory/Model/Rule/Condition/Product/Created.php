<?php
/**
 * @category    Bubble
 * @package     Bubble_DynamicCategory
 * @version     2.4.2
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_DynamicCategory_Model_Rule_Condition_Product_Created
    extends Bubble_DynamicCategory_Model_Rule_Condition_Product
{
    protected $_inputType = 'string';

    protected function _construct()
    {
        parent::_construct();
        $this->setType('product_created');
        $this->setValueName('Created');
    }

    public function getInputType()
    {
        return $this->_inputType;
    }

    public function getAttributeName()
    {
        return Mage::helper('dynamic_category')->__('Created');
    }

    public function getDefaultOperatorOptions()
    {
        if (null === $this->_defaultOperatorOptions) {
            $this->_defaultOperatorOptions = array(
                '<='  => Mage::helper('dynamic_category')->__('during the last'),
            );
        }

        return $this->_defaultOperatorOptions;
    }

    public function asHtml()
    {
        $html = $this->getTypeElementHtml()
            . $this->getAttributeElementHtml()
            . $this->getOperatorElementHtml()
            . $this->getValueElementHtml()
            . Mage::helper('dynamic_category')->__('days')
            . $this->getRemoveLinkHtml()
            . $this->getChooserContainerHtml();

        return $html;
    }

    public function validate(Varien_Object $object)
    {
        $createdAt = Mage::getSingleton('core/locale')->date($object->getCreatedAt(), Zend_Date::ISO_8601);
        $now = Mage::getSingleton('core/locale')->date();
        $diff = $now->sub($createdAt)->toValue();
        $days = floor($diff / 60 / 60 / 24);

        return $days <= $this->getValue();
    }
}