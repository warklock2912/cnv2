<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */

/** @var Mage_Eav_Model_Entity_Setup $installer */
$installer = $this;

$installer->startSetup();

$installer->run("  
CREATE TABLE IF NOT EXISTS `{$this->getTable('amseogooglesitemap/sitemap')}` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `folder_name` varchar(255) NOT NULL DEFAULT '',
  `max_items` smallint(6) NOT NULL DEFAULT '0',  
  `max_file_size` int(6) NOT NULL DEFAULT '0',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `last_run` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `stores` varchar(255) NOT NULL DEFAULT '',  
  `categories` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `categories_thumbs` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `categories_captions` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `categories_priority` varchar(3) NOT NULL DEFAULT '',
  `categories_frequency` varchar(16) NOT NULL DEFAULT '',
  `pages` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `pages_priority` varchar(3) NOT NULL DEFAULT '',
  `pages_frequency` varchar(16) NOT NULL DEFAULT '',
  `pages_modified` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `exclude_cms_aliases` text NOT NULL,
  `tags` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `tags_priority` varchar(3) NOT NULL DEFAULT '',
  `tags_frequency` varchar(16) NOT NULL DEFAULT '',
  `extra` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `extra_priority` varchar(3) NOT NULL DEFAULT '',
  `extra_frequency` varchar(16) NOT NULL DEFAULT '',  
  `extra_links` text,  
  `products` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `products_thumbs` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `products_captions` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `products_captions_template` varchar(1024) NOT NULL DEFAULT '',
  `products_priority` varchar(3) NOT NULL DEFAULT '',
  `products_frequency` varchar(16) NOT NULL DEFAULT '',
  `products_modified` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `products_url` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `landing` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `landing_priority` varchar(3) NOT NULL DEFAULT '',
  `landing_frequency` varchar(16) NOT NULL DEFAULT '',
  `brands` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `brands_priority` varchar(3) NOT NULL DEFAULT '',
  `brands_frequency` varchar(16) NOT NULL DEFAULT '',
  
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$googleSettings = array(
	'categories_frequency' => 'sitemap/category/changefreq',
	'categories_priority'  => 'sitemap/category/priority',
	'products_frequency'   => 'sitemap/product/changefreq',
	'products_priority'    => 'sitemap/product/priority',
	'pages_frequency'      => 'sitemap/page/changefreq',
	'pages_priority'       => 'sitemap/page/priority',

	'tags_frequency'       => 'sitemap/generate/frequency',
	'extra_frequency'      => 'sitemap/generate/frequency',
	'landing_frequency'    => 'sitemap/generate/frequency',
	'brands_frequency'     => 'sitemap/generate/frequency',

	'landing'              => 'sitemap/generate/enabled',
	'brands'               => 'sitemap/generate/enabled',
	'products'             => 'sitemap/generate/enabled',
	'categories'           => 'sitemap/generate/enabled',
	'pages'                => 'sitemap/generate/enabled',
	'tags'                 => 'sitemap/generate/enabled',
	'extra'                => 'sitemap/generate/enabled'
);

$def = array(
	'categories_frequency' => '',
	'categories_priority'  => '',
	'products_frequency'   => '',
	'products_priority'    => '',
	'pages_frequency'      => '',
	'pages_priority'       => '',
	'tags_frequency'       => '',
	'landing_frequency'    => '',
	'brands_frequency'     => '',
	'extra_frequency'      => '',
	'products'             => '0',
	'landing'              => '0',
	'categories'           => '0',
	'pages'                => '0',
	'tags'                 => '0',
	'extra'                => '0',
);

Mage::app()->reinitStores();
$stores = array();
foreach (Mage::app()->getStores() as $store) {
	$stores[] = $store;
}

$settingsToSave = array();
$connection     = $installer->getConnection();
foreach ($stores as $store) {
	$storeId     = $store->getId();
	$storeConfig = $store->getCode() != 'default' ? (int) $storeId : 0;
	$scope       = $connection->quote($store->getCode() != 'default' ? 'stores' : 'default');

	foreach ($googleSettings as $newColumn => $oldSetting) {
		$oldSettingQuoted = $connection->quote($oldSetting);

		$cnfValue = $connection->fetchOne("
			SELECT value FROM `{$this->getTable('core/config_data')}` WHERE path = $oldSettingQuoted AND scope_id = $storeConfig AND scope = $scope
		");

		if (! $cnfValue) {
			$cnfValue = (string) Mage::getConfig()->getNode('default/' . $oldSetting);
		}

		if ($oldSetting == 'sitemap/generate/frequency') {
			switch ($cnfValue) {
				case 'M' :
					$cnfValue = 'monthly';
					break;

				case 'D' :
					$cnfValue = 'daily';
					break;

				case 'W' :
					$cnfValue = 'weekly';
					break;
			}
		}

		if ($cnfValue) {
			if (! isset($settingsToSave[$storeId])) {
				$settingsToSave[$storeId] = array();
			}

			$settingsToSave[$storeId][$newColumn] = $cnfValue;
		}
	}
}

foreach ($settingsToSave as $storeId => $data) {
	$data['title']       = 'Imported From Google Sitemap Settings';
	$data['folder_name'] = 'media/google_sitemap_' . $storeId . '.xml';
	$data['stores']      = $storeId;

	$installer->getConnection()
		->insert($this->getTable('amseogooglesitemap/sitemap'), $data);
}


$installer->endSetup();