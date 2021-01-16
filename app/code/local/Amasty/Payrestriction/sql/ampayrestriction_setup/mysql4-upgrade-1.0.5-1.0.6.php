<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Payrestriction
 */
$this->startSetup();

$this->run("
ALTER TABLE `{$this->getTable('ampayrestriction/rule')}`  ADD `out_of_stock`  tinyint(1) unsigned NOT NULL default '0' AFTER `name`;
"); 

$this->endSetup();