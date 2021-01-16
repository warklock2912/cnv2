<?php

/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteSolr
 * @copyright  Copyright (c) 2014 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */
/**
 * Search Suite extension
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteSolr
 * @author     MageWorx Dev Team
 */
/* @var $installer MageWorx_SearchSuiteSolr_Model_Mysql4_Setup */
$installer = $this;
$installer->startSetup();

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
