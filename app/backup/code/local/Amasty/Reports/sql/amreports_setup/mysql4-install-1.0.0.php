<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE `{$this->getTable('amreports/data')}` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `report_type` varchar(50) DEFAULT NULL,
    `forms_data` longtext,
    `json_answer` longtext,
    `update_date` datetime NOT NULL,
    PRIMARY KEY  (`id`)
)

COLLATE='utf8_general_ci'
ENGINE=MyISAM
;
");

$installer->endSetup();