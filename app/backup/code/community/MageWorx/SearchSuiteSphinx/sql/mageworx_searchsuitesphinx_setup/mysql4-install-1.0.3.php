<?php
/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteSphinx
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

/* @var $installer MageWorx_SearchSuiteSphinx_Model_Mysql4_Setup */
$installer = $this;
$installer->startSetup();

// 1.0.2

if ($installer->tableExists($this->getTable('searchsuite_update_index')) && !$installer->tableExists($this->getTable('mageworx_searchsuite/update_index'))) {
    $installer->run("RENAME TABLE {$this->getTable('searchsuite_update_index')} TO {$this->getTable('mageworx_searchsuite/update_index')};");
}

$installer->run("
CREATE TABLE IF NOT EXISTS {$installer->getTable('mageworx_searchsuite/update_index')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fulltext_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
");
$installer->endSetup();
