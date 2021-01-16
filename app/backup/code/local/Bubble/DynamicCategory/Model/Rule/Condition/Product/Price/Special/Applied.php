<?php
/**
 * @category    Bubble
 * @package     Bubble_DynamicCategory
 * @version     2.4.2
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_DynamicCategory_Model_Rule_Condition_Product_Price_Special_Applied
    extends Bubble_DynamicCategory_Model_Rule_Condition_Product_Boolean
{
    protected function _construct()
    {
        parent::_construct();
        $this->setType('product_special_price_applied');
        $this->setValueName('Special Price Applied');
    }

    public function getAttributeName()
    {
        return Mage::helper('dynamic_category')->__('Special Price Applied');
    }

    public function getDefaultOperatorOptions()
    {
        return array('=='  => Mage::helper('rule')->__('is'));
    }

    public function getValueSelectOptions()
    {
        return array(
            array('value' => 1, 'label' => Mage::helper('adminhtml')->__('Yes')),
        );
    }

    public function collectValidatedAttributes($productCollection)
    {
        $todayStartOfDayDate = Mage::app()->getLocale()->date()
            ->setTime('00:00:00')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $todayEndOfDayDate = Mage::app()->getLocale()->date()
            ->setTime('23:59:59')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('catalog/product_collection'); // do not use $productCollection
        $collection->addAttributeToFilter('special_from_date', array('or' => array(
                0 => array('date' => true, 'to' => $todayEndOfDayDate),
                1 => array('is' => new Zend_Db_Expr('null')))
            ), 'left')
            ->addAttributeToFilter('special_to_date', array('or' => array(
                0 => array('date' => true, 'from' => $todayStartOfDayDate),
                1 => array('is' => new Zend_Db_Expr('null')))
            ), 'left')
            ->addAttributeToFilter(
                array(
                    array('attribute' => 'special_from_date', 'is' => new Zend_Db_Expr('not null')),
                    array('attribute' => 'special_to_date', 'is' => new Zend_Db_Expr('not null'))
                )
            );

        $this->_entityAttributeValues = array_flip($collection->getAllIds());

        return $this;
    }

    public function validate(Varien_Object $object)
    {
        return isset($this->_entityAttributeValues[$object->_getData('entity_id')]);
    }
}