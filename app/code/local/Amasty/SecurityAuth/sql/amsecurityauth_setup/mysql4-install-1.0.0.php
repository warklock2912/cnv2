<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SecurityAuth
 */

/* @var $this Mage_Core_Model_Resource_Setup */
$this->startSetup();

/**
 * create table Notification
 */

$userAdminTable = $this->getConnection()->describeTable($this->getTable('admin/user'));
$this->run("
  CREATE TABLE IF NOT EXISTS `{$this->getTable('amsecurityauth/user_auth')}` (
  `user_id` {$userAdminTable['user_id']['DATA_TYPE']} unsigned NOT NULL COMMENT 'Id',
  `enable` smallint(5) unsigned DEFAULT '0' COMMENT 'Enable two step verification',
  `two_factor_token` text COMMENT 'Token for two step verification',
  PRIMARY KEY (`user_id`),
  KEY `IDX_AMASTY_SECURITYAUTH_ADMIN_USER_USER_ID` (`user_id`),
  KEY `IDX_CORE_STORE_STORE_ID` (`user_id`),
  CONSTRAINT `FK_AMASTY_SECURITYAUTH_ADMIN_USER_USER_ID_ADMIN_USER_USER_ID` FOREIGN KEY (`user_id`) REFERENCES `{$this->getTable('admin/user')}` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Index Table revenue';
");

$this->endSetup();
