<?php

$installer = $this;
$installer->startSetup();
$setup = new Mage_Eav_Model_Entity_Setup('catalog_setup');
try {
    $attr = array(
        'group' => 'General Information',
        'type' => 'datetime',
        'input' => 'datetime',
        'label' => 'Counting Down',
        'backend' => 'eav/entity_attribute_backend_time_created',
        'frontend' => '',
        'source' => '',
        'visible' => 1,
        'user_defined' => 1,
        'used_for_price_rules' => 1,
        'position' => 2,
        'unique' => 0,
        'default' => '',
        'sort_order' => 110,
        'required' => false,
    );
    $setup->addAttribute('catalog_category', 'counting_downs', $attr);
    $giftAmount = Mage::getModel('catalog/resource_eav_attribute')->load($setup->getAttributeId('catalog_category', 'counting_downs'));
    $giftAmount->addData(array(
        'is_global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'is_configurable' => 1,
        'is_searchable' => 1,
        'is_visible_in_advanced_search' => 1,
        'is_comparable' => 0,
        'is_filterable' => 0,
        'is_required' => 0,
        'is_visible' => 1,
        'is_filterable_in_search' => 1,
        'is_used_for_promo_rules' => 1,
        'is_html_allowed_on_front' => 0,
        'is_visible_on_front' => 0,
        'used_in_product_listing' => 1,
        'used_for_sort_by' => 0,
    ))->save();
} catch (Exception $e) {
    
}
$installer->run("");
$installer->endSetup();
