<?php

$installer = $this;
$installer->startSetup();


$setup = Mage::getModel('eav/entity_setup', 'core_setup');
$setup->removeAttribute('catalog_product', 'product_text');


$entityTypeId = $setup->getEntityTypeId('catalog_product');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General');
$setup->addAttribute(
    'catalog_product','product_sort', array(
        'input'            => 'text',
        'type'             => 'int',
        'label'            => 'Product Sort',
        'visible'       => true,
        'required'      => false,
        'visible_on_front' => 1,
        'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'default'       => 0,
    )
);


$installer->endSetup();
