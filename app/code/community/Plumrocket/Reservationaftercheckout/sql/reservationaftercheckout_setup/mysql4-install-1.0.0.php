<?php


$installer = $this;
$installer->startSetup();
$setup = Mage::getModel('eav/entity_setup', 'core_setup');

$entityTypeId = $setup->getEntityTypeId('catalog_product');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General');

$setup->addAttribute(
    'catalog_product', 'reserved_after_order', array(
    'group'             => 'General',
    'input'             => 'hidden',
    'visible'           => 0,
    'required'          => 0,
    'visible_on_front'  => 1,
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    )
);
$setup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, 'reserved_after_order', '250');
$setup->addAttributeToSet($entityTypeId, $attributeSetId, $attributeGroupId, 'reserved_after_order', '250');


$installer->endSetup();