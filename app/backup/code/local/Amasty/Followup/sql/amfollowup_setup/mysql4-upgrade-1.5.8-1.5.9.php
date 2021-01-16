<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('amfollowup/schedule')}`
    ADD COLUMN `use_rule` BOOLEAN DEFAULT FALSE NOT NULL,
    ADD COLUMN `sales_rule_id` INT(10) UNSIGNED DEFAULT NULL;

");

$this->run("
    ALTER TABLE `{$this->getTable('amfollowup/history')}`
    ADD COLUMN `coupon_id` INT(10) UNSIGNED DEFAULT NULL;
");

$this->endSetup();