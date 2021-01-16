<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

/** @var $this Mage_Catalog_Model_Resource_Setup */
$this->startSetup();

$profileTable = $this->getTable('ecommerceteam_dataflow/profile_import');
$scheduleTable = $this->getTable('ecommerceteam_dataflow/profile_schedule');

$sql = "CREATE TABLE IF NOT EXISTS `{$profileTable}` (
  `entity_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `adapter_model` VARCHAR(255) NOT NULL,
  `parser_model` VARCHAR(255) NOT NULL,
  `column_delimiter` varchar(3) NOT NULL DEFAULT \",\",
  `can_create_new_entity` TINYINT(1) NOT NULL,
  `can_create_options` TINYINT(1) NOT NULL,
  `option_correction_percent` SMALLINT(3) NOT NULL,
  `option_delimiter` varchar(3) NOT NULL DEFAULT \",\",
  `can_create_categories` TINYINT(1) NOT NULL,
  `can_download_media` TINYINT(1) NOT NULL,
  `custom_column_mapping` TINYINT(1) NOT NULL,
  `schedule` varchar (32) NOT NULL,
  `schedule_config` text NOT NULL,
  `mapping` TEXT NOT NULL,
  PRIMARY KEY (`entity_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8";

$this->run($sql);

$sql = "CREATE TABLE IF NOT EXISTS `{$scheduleTable}` (
  `schedule_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Schedule Id',
  `profile_id` int(10) unsigned NOT NULL COMMENT 'Profile Id',
  `status` varchar(7) NOT NULL DEFAULT 'pending' COMMENT 'Status',
  `messages` text COMMENT 'Messages',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Created At',
  `scheduled_at` timestamp NULL DEFAULT NULL COMMENT 'Scheduled At',
  `executed_at` timestamp NULL DEFAULT NULL COMMENT 'Executed At',
  `finished_at` timestamp NULL DEFAULT NULL COMMENT 'Finished At',
  PRIMARY KEY (`schedule_id`),
  KEY `IDX_CRON_SCHEDULE_PROFILE_ID` (`schedule_id`),
  KEY `IDX_CRON_SCHEDULE_SCHEDULED_AT_STATUS` (`scheduled_at`,`status`),
  KEY `ET_DATAFLOW_PROFILE_SCHEDULE_ID` (`profile_id`),
  CONSTRAINT `ET_DATAFLOW_PROFILE_SCHEDULE_ID` FOREIGN KEY (`profile_id`) REFERENCES `{$profileTable}` (`entity_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Cron Schedule'";

$this->run($sql);

$this->endSetup();