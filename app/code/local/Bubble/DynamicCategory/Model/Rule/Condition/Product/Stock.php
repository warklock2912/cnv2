<?php
/**
 * @category    Bubble
 * @package     Bubble_DynamicCategory
 * @version     2.4.2
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_DynamicCategory_Model_Rule_Condition_Product_Stock
    extends Bubble_DynamicCategory_Model_Rule_Condition_Product_Boolean
{
    protected function _construct()
    {
        parent::_construct();
        $this->setType('product_stock');
        $this->setValueName('In Stock');
    }

    public function getAttributeName()
    {
        return Mage::helper('dynamic_category')->__('In Stock');
    }

    public function collectValidatedAttributes($productCollection)
    {
        /** @var $adapter Varien_Db_Adapter_Pdo_Mysql */
        $adapter = Mage::getResourceSingleton('core/resource')->getReadConnection();
        /** @var $collection Mage_CatalogInventory_Model_Resource_Stock_Item_Collection */
        $collection = Mage::getResourceModel('cataloginventory/stock_item_collection');
        $select = $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('product_id', 'is_in_stock'));

        $this->_entityAttributeValues = $adapter->fetchPairs($select);

        return $this;
    }
}