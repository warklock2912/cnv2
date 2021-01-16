<?php
/**
 * @category    Bubble
 * @package     Bubble_DynamicCategory
 * @version     2.4.2
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_DynamicCategory_Model_Rule_Condition_Product_HasImage
    extends Bubble_DynamicCategory_Model_Rule_Condition_Product_Boolean
{
    protected function _construct()
    {
        parent::_construct();
        $this->setType('has_image');
        $this->setValueName('Has Image');
    }

    public function getAttributeName()
    {
        return Mage::helper('dynamic_category')->__('Has Image');
    }

    public function collectValidatedAttributes($productCollection)
    {
        /** @var $adapter Varien_Db_Adapter_Pdo_Mysql */
        $adapter = Mage::getResourceSingleton('core/resource')->getReadConnection();
        $resource = Mage::getSingleton('core/resource');
        $select = $adapter->select()
            ->from(
                array('e' => $resource->getTableName('catalog_product_entity')),
                'entity_id'
            )
            ->joinLeft(
                array('mg' => $resource->getTableName('catalog/product_attribute_media_gallery')),
                'e.entity_id = mg.entity_id'
            )
            ->where('mg.value IS NULL');

        $this->_entityAttributeValues = array_flip($adapter->fetchCol($select));

        return $this;
    }

    public function validate(Varien_Object $object)
    {
        $hasImage = !isset($this->_entityAttributeValues[$object->_getData('entity_id')]);

        return $this->getValue() ? $hasImage : !$hasImage;
    }
}