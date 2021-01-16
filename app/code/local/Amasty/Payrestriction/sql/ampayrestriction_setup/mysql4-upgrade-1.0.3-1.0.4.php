<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Payrestriction
 */
$this->startSetup();

$this->run("
ALTER TABLE `{$this->getTable('ampayrestriction/rule')}`  ADD `days` varchar(255) NOT NULL default '' AFTER `name`;
"); 

$this->endSetup();