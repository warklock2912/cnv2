<?php
/**
 * @category    Bubble
 * @package     Bubble_DynamicCategory
 * @version     2.4.2
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_DynamicCategory_Model_Rule_Condition_Product_TotalChildQty
    extends Bubble_DynamicCategory_Model_Rule_Condition_Product
{
    protected $_inputType = 'numeric';

    protected function _construct()
    {
        parent::_construct();
        $this->setType('total_child_products_qty');
        $this->setValueName('Total Child Products Quantity In Stock');
    }

    public function getAttributeName()
    {
        return Mage::helper('dynamic_category')->__('Total Child Products Quantity In Stock');
    }

    public function collectValidatedAttributes($productCollection)
    {
        /** @var $adapter Varien_Db_Adapter_Pdo_Mysql */
        $adapter = Mage::getResourceSingleton('core/resource')->getReadConnection();
        $select = $adapter->select()
            ->from(array('relation' => Mage::getResourceSingleton('catalog/product_relation')->getMainTable()), 'parent_id')
            ->joinLeft(
                array('stock' => Mage::getResourceSingleton('cataloginventory/stock_item')->getMainTable()),
                'relation.child_id = stock.product_id',
                array('total_child_qty' => new Zend_Db_Expr('SUM(stock.qty)'))
            )
            ->group('relation.parent_id');

        $this->_entityAttributeValues = array_map('floatval', $adapter->fetchPairs($select));

        return $this;
    }

    public function validate(Varien_Object $object)
    {
        $qty = 0;
        if (isset($this->_entityAttributeValues[$object->_getData('entity_id')])) {
            $qty = $this->_entityAttributeValues[$object->_getData('entity_id')];
        }
        $object->setData($this->_getData('attribute'), $qty);

        return $this->_validateProduct($object);
    }
}