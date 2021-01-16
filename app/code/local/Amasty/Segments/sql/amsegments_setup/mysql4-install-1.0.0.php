<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */

$this->startSetup();

$this->run("
CREATE TABLE `{$this->getTable('amsegments/cart')}` (
  `entity_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `quote_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`entity_id`),
  KEY `FK_AMSEGMENTS_AMCART_AMCUSTOMER` (`customer_id`),
  KEY `FK_AMSEGMENTS_AMCART_CART` (`quote_id`),
  CONSTRAINT `FK_AMSEGMENTS_AMCART_AMCUSTOMER` FOREIGN KEY (`customer_id`) REFERENCES `{$this->getTable('amsegments/customer')}` (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_AMSEGMENTS_AMCART_CART` FOREIGN KEY (`quote_id`) REFERENCES `{$this->getTable('sales/quote')}` (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `{$this->getTable('amsegments/customer')}` (
  `entity_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_firstname` varchar(255) DEFAULT NULL,
  `customer_lastname` varchar(255) DEFAULT NULL,
  `website_id` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`entity_id`),
  UNIQUE KEY `KEY_AMSEGMENTS_CUSTOMER_UNIQUE` (`customer_email`,`website_id`),
  KEY `FK_AMSEGMENTS_CUSTOMER_ENTITY_CUSTOMER_ID_CUSTOMER_ENTITY_ID` (`customer_id`),
  KEY `FK_AMSEGMENTS_CUSTOMER_ENTITY_WEBSITE_ID_CORE_WEBSITE_WEBSITE_ID` (`website_id`),
  CONSTRAINT `FK_AMSEGMENTS_CUSTOMER_ENTITY_CUSTOMER_ID_CUSTOMER_ENTITY_ID` FOREIGN KEY (`customer_id`) REFERENCES `{$this->getTable('customer/entity')}` (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_AMSEGMENTS_CUSTOMER_ENTITY_WEBSITE_ID_CORE_WEBSITE_WEBSITE_ID` FOREIGN KEY (`website_id`) REFERENCES `{$this->getTable('core/website')}` (`website_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `{$this->getTable('amsegments/index')}` (
  `entity_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `segment_id` int(10) unsigned DEFAULT NULL,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `level` varchar(255) DEFAULT NULL,
  `parent` varchar(255) DEFAULT NULL,
  `result` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`entity_id`),
  KEY `FK_{$this->getTable('amsegments/index')}_customer` (`customer_id`),
  KEY `FK_{$this->getTable('amsegments/index')}_segment` (`segment_id`),
  KEY `KEY_{$this->getTable('amsegments/index')}_level` (`level`),
  KEY `KEY_{$this->getTable('amsegments/index')}_parent` (`parent`),
  CONSTRAINT `FK_{$this->getTable('amsegments/index')}_customer` FOREIGN KEY (`customer_id`) REFERENCES `{$this->getTable('amsegments/customer')}` (`entity_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_{$this->getTable('amsegments/index')}_segment` FOREIGN KEY (`segment_id`) REFERENCES `{$this->getTable('amsegments/segment')}` (`segment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `{$this->getTable('amsegments/order')}` (
  `entity_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `sales_order_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`entity_id`),
  KEY `FK_AMSEGMENTS_AMORDER_AMCUSTOMER` (`customer_id`),
  KEY `FK_AMSEGMENTS_AMORDER_ORDER` (`sales_order_id`),
  CONSTRAINT `FK_AMSEGMENTS_AMORDER_AMCUSTOMER` FOREIGN KEY (`customer_id`) REFERENCES `{$this->getTable('amsegments/customer')}` (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_AMSEGMENTS_AMORDER_ORDER` FOREIGN KEY (`sales_order_id`) REFERENCES `{$this->getTable('sales/order')}` (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `{$this->getTable('amsegments/product_index')}` (
  `entity_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `segment_id` int(10) unsigned DEFAULT NULL,
  `order_id` int(10) unsigned DEFAULT NULL,
  `level` varchar(255) DEFAULT NULL,
  `parent` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`entity_id`),
  KEY `FK_{$this->getTable('amsegments/product_index')}_segment` (`segment_id`),
  KEY `KEY_{$this->getTable('amsegments/product_index')}_level` (`level`),
  KEY `KEY_{$this->getTable('amsegments/product_index')}_parent` (`parent`),
  KEY `KEY_{$this->getTable('amsegments/product_index')}_order_id` (`order_id`),
  CONSTRAINT `FK_{$this->getTable('amsegments/product_index')}_segment` FOREIGN KEY (`segment_id`) REFERENCES `{$this->getTable('amsegments/segment')}` (`segment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `{$this->getTable('amsegments/segment')}` (
  `segment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT '',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `website_id` SMALLINT(5) UNSIGNED NOT NULL,
  `conditions_serialized` text,
  `created_at` datetime DEFAULT NULL,
  `generated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`segment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");
$this->endSetup();

