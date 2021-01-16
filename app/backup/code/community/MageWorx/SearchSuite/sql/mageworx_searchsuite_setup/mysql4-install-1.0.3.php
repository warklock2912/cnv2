<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
/* @var $installer MageWorx_SearchSuite_Model_Mysql4_Setup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

// 1.0.3

if ($installer->tableExists($this->getTable('searchsuite_purchase_tracking')) && !$installer->tableExists($this->getTable('mageworx_searchsuite/purchase_tracking'))) {
    $installer->run("RENAME TABLE {$this->getTable('searchsuite_purchase_tracking')} TO {$this->getTable('mageworx_searchsuite/purchase_tracking')};");
}

if ($installer->tableExists($this->getTable('searchsuite_conversion_tracking')) && !$installer->tableExists($this->getTable('mageworx_searchsuite/conversion_tracking'))) {
    $installer->run("RENAME TABLE {$this->getTable('searchsuite_conversion_tracking')} TO {$this->getTable('mageworx_searchsuite/conversion_tracking')};");
}

if ($installer->tableExists($this->getTable('searchsuite_region_tracking')) && !$installer->tableExists($this->getTable('mageworx_searchsuite/region_tracking'))) {
    $installer->run("RENAME TABLE {$this->getTable('searchsuite_region_tracking')} TO {$this->getTable('mageworx_searchsuite/region_tracking')};");
}

if ($installer->tableExists($this->getTable('searchsuite_category_fulltext')) && !$installer->tableExists($this->getTable('mageworx_searchsuite/category_fulltext'))) {
    $installer->run("RENAME TABLE {$this->getTable('searchsuite_category_fulltext')} TO {$this->getTable('mageworx_searchsuite/category_fulltext')};");
}

if ($installer->tableExists($this->getTable('searchsuite_category_result')) && !$installer->tableExists($this->getTable('mageworx_searchsuite/category_result'))) {
    $installer->run("RENAME TABLE {$this->getTable('searchsuite_category_result')} TO {$this->getTable('mageworx_searchsuite/category_result')};");
}

if ($installer->tableExists($this->getTable('searchsuite_cmspage_fulltext')) && !$installer->tableExists($this->getTable('mageworx_searchsuite/cmspage_fulltext'))) {
    $installer->run("RENAME TABLE {$this->getTable('searchsuite_cmspage_fulltext')} TO {$this->getTable('mageworx_searchsuite/cmspage_fulltext')};");
}

if ($installer->tableExists($this->getTable('searchsuite_cmspage_result')) && !$installer->tableExists($this->getTable('mageworx_searchsuite/cmspage_result'))) {
    $installer->run("RENAME TABLE {$this->getTable('searchsuite_cmspage_result')} TO {$this->getTable('mageworx_searchsuite/cmspage_result')};");
}

if ($installer->tableExists($this->getTable('searchsuite_awblog_fulltext')) && !$installer->tableExists($this->getTable('mageworx_searchsuite_awblog_fulltext'))) {
    $installer->run("RENAME TABLE {$this->getTable('searchsuite_awblog_fulltext')} TO {$this->getTable('mageworx_searchsuite_awblog_fulltext')};");
}

if ($installer->tableExists($this->getTable('searchsuite_awblog_result')) && !$installer->tableExists($this->getTable('mageworx_searchsuite_awblog_result'))) {
    $installer->run("RENAME TABLE {$this->getTable('searchsuite_awblog_result')} TO {$this->getTable('mageworx_searchsuite_awblog_result')};");
}

if ($installer->tableExists($this->getTable('searchsuite_synonyms')) && !$installer->tableExists($this->getTable('mageworx_searchsuite/synonyms'))) {
    $installer->run("RENAME TABLE {$this->getTable('searchsuite_synonyms')} TO {$this->getTable('mageworx_searchsuite/synonyms')};");
}

if ($installer->tableExists($this->getTable('searchsuite_stopwords')) && !$installer->tableExists($this->getTable('mageworx_searchsuite/stopwords'))) {
    $installer->run("RENAME TABLE {$this->getTable('searchsuite_stopwords')} TO {$this->getTable('mageworx_searchsuite/stopwords')};");
}


// 1.0.0

if (!$connection->tableColumnExists($installer->getTable('catalog/eav_attribute'), 'quick_search_priority')) {
    $connection->addColumn($installer->getTable('catalog/eav_attribute'), 'quick_search_priority', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT \'5\'');
}

if (!$connection->tableColumnExists($installer->getTable('catalog/eav_attribute'), 'is_attributes_search')) {
    $connection->addColumn($installer->getTable('catalog/eav_attribute'), 'is_attributes_search', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT \'0\'');
}

$installer->addAttribute('catalog_category', 'use_in_quicksearch', array(
    'type' => 'int',
    'label' => 'Use In Quicksearch',
    'input' => 'select',
    'source' => 'eav/entity_attribute_source_boolean',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'required' => false,
    'default' => 0
));

$fulltextTable = $installer->getTable('catalogsearch/fulltext');
if (!$connection->tableColumnExists($fulltextTable, 'data_index5')) {
    $connection->delete($fulltextTable);
    $connection->addColumn($fulltextTable, 'data_index1', 'longtext NOT NULL');
    $connection->addColumn($fulltextTable, 'data_index2', 'longtext NOT NULL');
    $connection->addColumn($fulltextTable, 'data_index3', 'longtext NOT NULL');
    $connection->addColumn($fulltextTable, 'data_index4', 'longtext NOT NULL');
    $connection->addColumn($fulltextTable, 'data_index5', 'longtext NOT NULL');

    $connection->addKey($fulltextTable, 'data_findex_1', 'data_index1', 'fulltext');
    $connection->addKey($fulltextTable, 'data_findex_2', 'data_index2', 'fulltext');
    $connection->addKey($fulltextTable, 'data_findex_3', 'data_index3', 'fulltext');
    $connection->addKey($fulltextTable, 'data_findex_4', 'data_index4', 'fulltext');
    $connection->addKey($fulltextTable, 'data_findex_5', 'data_index5', 'fulltext');

    $installer->updateAttributes();
}
$installer->rebuildIndex();

$catalogsearchQueryTable = $installer->getTable('catalogsearch_query');

$installer->run("
CREATE TABLE IF NOT EXISTS `{$installer->getTable('mageworx_searchsuite/purchase_tracking')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `query_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `price` decimal(12,4)	NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='';

CREATE TABLE IF NOT EXISTS `{$installer->getTable('mageworx_searchsuite/conversion_tracking')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `query_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='';

CREATE TABLE IF NOT EXISTS `{$installer->getTable('mageworx_searchsuite/region_tracking')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `query_id` int(11) NOT NULL,
  `country` varchar(4) NOT NULL,
  `num_uses` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='';

DROP TABLE IF EXISTS `{$installer->getTable('mageworx_searchsuite/category_fulltext')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('mageworx_searchsuite/category_fulltext')}` (
  `category_id` int(10) unsigned NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  `data_index` longtext NOT NULL,
  PRIMARY KEY (`category_id`,`store_id`),
  FULLTEXT KEY `data_index` (`data_index`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('mageworx_searchsuite/category_result')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('mageworx_searchsuite/category_result')}` (
  `query_id` int(10) unsigned NOT NULL,
  `category_id` smallint(6) NOT NULL,
  `relevance` decimal(6,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`query_id`,`category_id`),
  KEY `IDX_QUERY` (`query_id`),
  KEY `IDX_CATEGORY` (`category_id`),
  KEY `IDX_RELEVANCE` (`query_id`, `relevance`),
  CONSTRAINT `FK_SEARCHSUITE_CATEGORY_RESULT_QUERY` FOREIGN KEY (`query_id`) REFERENCES `{$catalogsearchQueryTable}` (`query_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('mageworx_searchsuite/cmspage_fulltext')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('mageworx_searchsuite/cmspage_fulltext')}` (
  `page_id` int(10) unsigned NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  `data_index` longtext NOT NULL,
  PRIMARY KEY (`page_id`,`store_id`),
  FULLTEXT KEY `data_index` (`data_index`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('mageworx_searchsuite/cmspage_result')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('mageworx_searchsuite/cmspage_result')}` (
  `query_id` int(10) unsigned NOT NULL,
  `page_id` smallint(6) NOT NULL,
  `relevance` decimal(6,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`query_id`,`page_id`),
  KEY `IDX_QUERY` (`query_id`),
  KEY `IDX_PAGE` (`page_id`),
  KEY `IDX_RELEVANCE` (`query_id`, `relevance`),
  CONSTRAINT `FK_SEARCHSUITE_CMSPAGE_RESULT_QUERY` FOREIGN KEY (`query_id`) REFERENCES `{$catalogsearchQueryTable}` (`query_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('mageworx_searchsuite_awblog_fulltext')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('mageworx_searchsuite_awblog_fulltext')}` (
 `post_id` int(10) unsigned NOT NULL,
 `store_id` smallint(5) unsigned NOT NULL,
 `data_index` longtext NOT NULL,
 PRIMARY KEY (`post_id`,`store_id`),
 FULLTEXT KEY `data_index` (`data_index`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `{$installer->getTable('mageworx_searchsuite_awblog_result')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('mageworx_searchsuite_awblog_result')}` (
  `query_id` int(10) unsigned NOT NULL,
  `post_id` smallint(6) NOT NULL,
  `relevance` decimal(6,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`query_id`,`post_id`),
  KEY `IDX_BLOG_QUERY` (`query_id`),
  KEY `IDX_BLOG_PAGE` (`post_id`),
  KEY `IDX_BLOG_RELEVANCE` (`query_id`, `relevance`),
  CONSTRAINT `FK_SEARCHSUITE_BLOG_RESULT_QUERY` FOREIGN KEY (`query_id`) REFERENCES `{$catalogsearchQueryTable}` (`query_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$installer->getTable('mageworx_searchsuite/synonyms')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `query_id` int(10) unsigned NOT NULL,
  `synonym` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='';

CREATE TABLE IF NOT EXISTS `{$installer->getTable('mageworx_searchsuite/stopwords')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` smallint(5) unsigned NOT NULL,
  `word` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT uc_stopword UNIQUE (store_id,word)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='';
");

if (!$connection->tableColumnExists($catalogsearchQueryTable, 'is_cmspage_processed')) {
    $connection->addColumn($catalogsearchQueryTable, 'is_cmspage_processed', 'tinyint(1) DEFAULT 0');
}
if (!$connection->tableColumnExists($catalogsearchQueryTable, 'is_category_processed')) {
    $connection->addColumn($catalogsearchQueryTable, 'is_category_processed', 'tinyint(1) DEFAULT 0');
    $connection->addColumn($catalogsearchQueryTable, 'is_awblog_processed', 'tinyint(1) DEFAULT 0');
}
if (!$connection->tableColumnExists($catalogsearchQueryTable, 'static_block')) {
    $connection->addColumn($catalogsearchQueryTable, 'static_block', 'varchar(255) DEFAULT NULL');
}

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'product_search_priority', array(
    'group' => 'General',
    'type' => 'decimal',
    'backend' => '',
    'frontend' => '',
    'label' => 'Product Search Priority',
    'input' => 'text',
    'class' => '',
    'source' => '',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible' => true,
    'required' => false,
    'user_defined' => false,
    'default' => '',
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => false,
    'unique' => false,
    'apply_to' => 'simple,configurable,bundle,grouped',
    'sort_order' => 120
));


// updating config paths

$installer->run("UPDATE IGNORE `{$this->getTable('core_config_data')}` SET `path` = REPLACE(`path`,'mageworx_searchsuite/search/','mageworx_searchsuite/main/') WHERE `path` LIKE 'mageworx_searchsuite/search/%'");


$installer->endSetup();
