<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoHtmlSitemap
 */

/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();

$attrCode      = 'am_hide_from_html_sitemap';
$attrGroupName = 'Meta Information';
$categoryAttrGroupName = 'General Information';
$attrLabel     = 'Hide from HTML Sitemap';

if ($installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, $attrCode) === false) {
	$installer->addAttribute(
		Mage_Catalog_Model_Product::ENTITY,
		$attrCode,
		array(
			 'group'            => $attrGroupName,
			 'type'             => 'int',
			 'input'            => 'select',
			 'source'           => 'eav/entity_attribute_source_boolean',
			 'backend'          => '',
			 'frontend'         => '',
			 'label'            => $attrLabel,
			 'note'             => '',
			 'class'            => '',
			 'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
			 'visible'          => true,
			 'required'         => false,
			 'user_defined'     => false,
			 'default'          => 0,
			 'visible_on_front' => false,
			 'unique'           => false,
			 'is_configurable'  => false,
			 'searchable'       => false,
			 'filterable'       => false,
			 'comparable'       => false,
             'used_in_product_listing' => true,
		)
	);
}

if ($installer->getAttributeId(Mage_Catalog_Model_Category::ENTITY, $attrCode) === false) {
	$installer->addAttribute(
		Mage_Catalog_Model_Category::ENTITY,
		$attrCode,
		array(
             'group'            => $categoryAttrGroupName,
			 'type'             => 'int',
			 'backend'          => '',
			 'frontend'         => '',
			 'label'            => $attrLabel,
			 'input'            => 'select',
			 'class'            => '',
			 'source'           => 'eav/entity_attribute_source_boolean',
			 'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
			 'visible'          => false,
			 'required'         => false,
			 'user_defined'     => false,
			 'default'          => 0,
			 'searchable'       => false,
			 'filterable'       => false,
			 'comparable'       => false,
			 'visible_on_front' => false,
			 'unique'           => false,
             'used_in_product_listing' => true,
		)
	);
}

$installer->endSetup();