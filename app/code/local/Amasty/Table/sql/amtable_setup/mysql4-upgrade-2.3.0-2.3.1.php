<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */
$installer = $this;

$installer->startSetup();

$installer->run("
  ALTER TABLE  `{$this->getTable('amtable/method')}`  ADD  `name_on_eng`  varchar(255) NOT NULL AFTER  `name`;
  ALTER TABLE `{$this->getTable('amtable/method')}` ADD `comment_on_engs_method_` TEXT NULL DEFAULT NULL AFTER `name`;
");

$installer->endSetup();