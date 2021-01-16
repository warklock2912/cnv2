<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */
$this->startSetup();

$this->run("ALTER TABLE {$this->getTable('amgroupcat/rules')} CHANGE `categories` `categories` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");

$this->endSetup();
