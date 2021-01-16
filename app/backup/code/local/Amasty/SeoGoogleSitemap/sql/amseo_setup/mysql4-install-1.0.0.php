<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */

$installer = $this;

$installer->startSetup();

$installer->run("  
CREATE TABLE IF NOT EXISTS `{$this->getTable('amseogooglesitemap/sitemap')}` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `folder_name` varchar(255) NOT NULL DEFAULT '',
  `max_items` smallint(6) NOT NULL DEFAULT '0',  
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

$installer->endSetup();