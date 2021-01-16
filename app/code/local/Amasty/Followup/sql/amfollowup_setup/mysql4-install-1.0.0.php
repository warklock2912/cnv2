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
    
CREATE TABLE `{$this->getTable('amfollowup/rule')}` (
  `rule_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `start_event_type` varchar(255) DEFAULT NULL,
  `cancel_event_type` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT '',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `to_subscribers` TINYINT(1) NOT NULL DEFAULT '0',
  `stores` varchar(255) NOT NULL DEFAULT '',
  `cust_groups` varchar(255) NOT NULL DEFAULT '',
  `sender_name` VARCHAR(255) NOT NULL DEFAULT '',
  `sender_email` VARCHAR(255) NOT NULL DEFAULT '',
  `sender_cc` VARCHAR(255) NOT NULL DEFAULT '',
  `utm_source` VARCHAR(255) NOT NULL DEFAULT '',
  `utm_medium` VARCHAR(255) NOT NULL DEFAULT '',
  `utm_term` VARCHAR(255) NOT NULL DEFAULT '',
  `utm_content` VARCHAR(255) NOT NULL DEFAULT '',
  `utm_campaign` VARCHAR(255) NOT NULL DEFAULT '',
  `conditions_serialized` text,
  PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amfollowup/link')}` (
  `link_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `history_id` int(10) unsigned DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`link_id`),
  KEY `amasty_amfollowup_link_customer_entity` (`customer_id`),
  CONSTRAINT `FK_am_followup_link_customer_entity` FOREIGN KEY (`customer_id`) REFERENCES `{$this->getTable('customer/entity')}` (`entity_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amfollowup/blacklist')}` (
  `blacklist_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` char(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`blacklist_id`),
  UNIQUE KEY `email` (`email`),
  KEY `IDX_EMAIL` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amfollowup/attribute')}` (
  `attr_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) unsigned NOT NULL,
  `code` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`attr_id`),
  KEY `FK_AMFOLLOWUP_RULE` (`rule_id`),
  CONSTRAINT `FK_AMFOLLOWUP_RULE` FOREIGN KEY (`rule_id`) REFERENCES `{$this->getTable('amfollowup/rule')}` (`rule_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amfollowup/schedule')}` (
  `schedule_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) unsigned NOT NULL,
  `email_template_id` int(10) unsigned DEFAULT NULL,
  `delayed_start` int(10) unsigned DEFAULT NULL,
  `coupon_type` enum('by_percent','by_fixed','cart_fixed') DEFAULT NULL,
  `discount_amount` int(5) unsigned NOT NULL DEFAULT '0',
  `expired_in_days` int(5) unsigned NOT NULL DEFAULT '0',
  `subtotal_greater_than` decimal(12,4) DEFAULT NULL,
  PRIMARY KEY (`schedule_id`),
  KEY `FK_amasty_amfollowup_schedule_amasty_amfollowup_rules` (`rule_id`),
  KEY `FK_amasty_amfollowup_schedule_core_email_template` (`email_template_id`),
  CONSTRAINT `FK_amasty_amfollowup_schedule_amasty_amfollowup_rules` FOREIGN KEY (`rule_id`) REFERENCES `{$this->getTable('amfollowup/rule')}` (`rule_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_amasty_amfollowup_schedule_core_email_template` FOREIGN KEY (`email_template_id`) REFERENCES `{$this->getTable('core/email_template')}` (`template_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amfollowup/history')}` (
  `history_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned DEFAULT NULL,
  `rule_id` int(10) unsigned DEFAULT NULL,
  `schedule_id` int(10) unsigned DEFAULT NULL,
  `store_id` smallint(5) unsigned DEFAULT NULL,
  `email` char(255) DEFAULT NULL,
  `increment_id` varchar(50) DEFAULT NULL,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `body` text,
  `subject` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `executed_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','processing','sent','cancel') DEFAULT NULL,
  `reason` enum('blacklist', 'not_subsribed', 'event','admin') DEFAULT NULL,
  `public_key` char(255) DEFAULT NULL,
  `sales_rule_id` int(10) unsigned DEFAULT NULL,
  `coupon_code` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`history_id`),
  KEY `FK_amasty_amfollowup_history_amasty_amfollowup_schedule` (`schedule_id`),
  KEY `FK_amasty_amfollowup_history_sales_flat_order` (`order_id`),
  KEY `IDX_STATUS` (`status`),
  KEY `IDX_EMAIL` (`email`),
  KEY `FK_amasty_amfollowup_history_customer_entity` (`customer_id`),
  KEY `FK_amasty_amfollowup_history_core_store` (`store_id`),
  KEY `FK_amasty_amfollowup_history_salesrule` (`sales_rule_id`),
  KEY `FK_amasty_amfollowup_history_amasty_amfollowup_rule` (`rule_id`),
  CONSTRAINT `FK_amasty_amfollowup_history_core_store` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_amasty_amfollowup_history_customer_entity` FOREIGN KEY (`customer_id`) REFERENCES `{$this->getTable('customer/entity')}` (`entity_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_amasty_amfollowup_history_salesrule` FOREIGN KEY (`sales_rule_id`) REFERENCES `{$this->getTable('salesrule/rule')}` (`rule_id`) ON DELETE SET NULL,
  CONSTRAINT `FK_amasty_amfollowup_history_sales_flat_order` FOREIGN KEY (`order_id`) REFERENCES `{$this->getTable('sales/order')}` (`entity_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

");

$this->endSetup(); 