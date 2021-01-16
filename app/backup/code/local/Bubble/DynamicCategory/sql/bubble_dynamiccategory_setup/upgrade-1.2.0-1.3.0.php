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

/**
 * @deprecated since 2.0.0
 * This attribute is not needed anymore
 */
/*$installer->addAttribute('catalog_category', 'dynamic_products_count', array(
    'type'              => 'int',
    'backend'           => '',
    'input_renderer'    => '',
    'frontend'          => '',
    'label'             => '',
    'input'             => '',
    'class'             => '',
    'source'            => '',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => false,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '',
    'visible_on_front'  => false,
    'unique'            => false,
    'group'             => 'General Information',
));*/

$installer->endSetup();
