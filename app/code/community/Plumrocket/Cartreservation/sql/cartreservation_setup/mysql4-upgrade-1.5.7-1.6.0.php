<?php

$installer = $this;
$installer->startSetup();


$setup = Mage::getModel('eav/entity_setup', 'core_setup');

// ======================== CATEGORY

$entityTypeId = $setup->getEntityTypeId('catalog_category');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General Information');

// Add Enable Delivery
$setup->addAttribute(
    'catalog_category', 'cr_enable', array(
    'type'             => 'int',
    'label'            => 'Cart Reservation Status',
    'input'            => 'select',
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'       => true,
    'required'      => true,
    'user_defined'  => false,
    'source'        => 'cartreservation/attribute_source_enable',
    'default'       => 0,
    )
);
$setup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, 'cr_enable', '467');
$setup->addAttributeToSet($entityTypeId, $attributeSetId, $attributeGroupId, 'cr_enable', '467');

// ======================== PRODUCT

$entityTypeId = $setup->getEntityTypeId('catalog_product');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General');

// Add Enable Delivery
$setup->addAttribute(
    'catalog_product', 'cr_enable', array(
    'input'            => 'select',
    'type'             => 'int',
    'label'            => 'Cart Reservation Status',
    'visible'       => true,
    'required'      => true,
    'visible_on_front' => 1,
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'default'       => 0,
    'source'        => 'cartreservation/attribute_source_enable',
    )
);
$setup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, 'cr_enable', '467');
$setup->addAttributeToSet($entityTypeId, $attributeSetId, $attributeGroupId, 'cr_enable', '467');


$installer->endSetup();
