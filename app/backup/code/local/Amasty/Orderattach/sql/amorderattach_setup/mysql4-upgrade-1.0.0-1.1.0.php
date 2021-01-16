<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattach
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
ALTER TABLE `{$this->getTable('amorderattach/field')}` ADD `options` TEXT NOT NULL AFTER `type` ;
");

$installer->endSetup(); 