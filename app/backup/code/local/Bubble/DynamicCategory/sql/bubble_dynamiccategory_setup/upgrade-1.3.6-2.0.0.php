<?php
/**
 * @category    Bubble
 * @package     Bubble_DynamicCategory
 * @version     2.4.2
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
/**
 * @var $this Mage_Catalog_Model_Resource_Setup
 */
$installer = $this;
$installer->startSetup();

// Delete this useless attribute if present
$installer->removeAttribute('catalog_category', 'dynamic_products_count');

$installer->addAttribute('catalog_category', 'dynamic_products_refresh', array(
    'type'              => 'int',
    'backend'           => '',
    'input_renderer'    => '',
    'frontend'          => '',
    'label'             => '',
    'input'             => 'select',
    'class'             => '',
    'source'            => 'eav/entity_attribute_source_boolean',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => false,
    'required'          => false,
    'user_defined'      => false,
    'default'           => 0,
    'visible_on_front'  => false,
    'unique'            => false,
    'group'             => 'General Information',
));

$installer->endSetup();
