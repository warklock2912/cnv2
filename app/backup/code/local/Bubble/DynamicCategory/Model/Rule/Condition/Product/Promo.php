<?php
/**
 * @category    Bubble
 * @package     Bubble_DynamicCategory
 * @version     2.4.2
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_DynamicCategory_Model_Rule_Condition_Product_Promo
    extends Bubble_DynamicCategory_Model_Rule_Condition_Product_Boolean
{
    protected function _construct()
    {
        parent::_construct();
        $this->setType('product_in_promo');
        $this->setValueName('In Promo');
    }

    public function getAttributeName()
    {
        return Mage::helper('dynamic_category')->__('In Promo');
    }

    public function getDefaultOperatorOptions()
    {
        return array('=='  => Mage::helper('rule')->__('is'));
    }

    public function getValueSelectOptions()
    {
        return array(
            array('value' => 1, 'label' => Mage::helper('adminhtml')->__('Yes')),
            array('value' => 0, 'label' => Mage::helper('adminhtml')->__('No')),
        );
    }

    public function collectValidatedAttributes($productCollection)
    {
        /** @var $adapter Varien_Db_Adapter_Pdo_Mysql */
        $resource = Mage::getSingleton('core/resource');
        $adapter = $resource->getConnection('read');
        $select = $adapter->select()
            ->from(array('price_index' => $resource->getTableName('catalog_product_index_price')), 'entity_id')
            ->group('entity_id')
            ->having(new Zend_Db_Expr('SUM(final_price < price) > 0'));

        $this->_entityAttributeValues = array_flip($adapter->fetchCol($select));

        return $this;
    }

    public function validate(Varien_Object $object)
    {
        $isPromo = isset($this->_entityAttributeValues[$object->_getData('entity_id')]);

        return $this->getValue() ? $isPromo : !$isPromo;
    }
}