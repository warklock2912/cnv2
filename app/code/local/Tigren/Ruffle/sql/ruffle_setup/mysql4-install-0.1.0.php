<?php
$installer = $this;
$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('ruffle')};
CREATE TABLE {$this->getTable('ruffle')} (
	`ruffle_id` int(11) unsigned NOT NULL auto_increment,
 	`title` varchar(255) NOT NULL default '',
  	`description` text NULL,
  	`announce_type` smallint(6) NOT NULL default '0',
  	`is_active` smallint(6) NOT NULL default '0',
  	`start_date` date NULL,
  	`end_date` date NULL,
  	`announce_date` date NULL,
  	PRIMARY KEY (`ruffle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('ruffle_product')};
CREATE TABLE {$this->getTable('ruffle_product')} (
	`rp_id` int(11) unsigned NOT NULL auto_increment,
 	`ruffle_id` int(11) unsigned NOT NULL,
 	`product_id` int(11) unsigned NOT NULL,
  	`vip_qty` int(11) unsigned NOT NULL,
  	`general_qty` int(11) unsigned NOT NULL,
  	`product_name` varchar(255) NULL default '',
  	`sku` varchar(255) NULL default '',
  	`stock_qty` int(11) NULL,
  	PRIMARY KEY (`rp_id`), 
  	UNIQUE KEY(`ruffle_id`, `product_id`),
  	CONSTRAINT `FK_RUFFLE_PRODUCT_RUFFLE_ID` FOREIGN KEY (`ruffle_id`) REFERENCES `{$this->getTable('ruffle')}` (`ruffle_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  	CONSTRAINT `FK_RUFFLE_PRODUCT_PRODUCT_ID` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog_product_entity')}` (`entity_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('ruffle_joiner')};
CREATE TABLE {$this->getTable('ruffle_joiner')} (
	`joiner_id` int(11) unsigned NOT NULL auto_increment,
 	`ruffle_id` int(11) unsigned NOT NULL,
 	`customer_id` int(11) unsigned NOT NULL,
 	`product_id` int(11) unsigned NOT NULL,
  	`customer_name` varchar(255) NULL default '',
  	`email_address` varchar(255) NULL default '',
  	`ruffle_number` varchar(255) NULL default '',
  	`telephone` varchar(255) NULL default '',
  	`product_name` varchar(255) NULL default '',
  	`product_sku` varchar(255) NULL default '',
  	`joined_date` date NULL,
  	PRIMARY KEY (`joiner_id`), 
  	UNIQUE KEY(`ruffle_id`, `customer_id`, `product_id`),
  	UNIQUE KEY(`ruffle_number`),
 	CONSTRAINT `FK_RUFFLE_JOINER_RUFFLE_ID` FOREIGN KEY (`ruffle_id`) REFERENCES `{$this->getTable('ruffle')}` (`ruffle_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    
");

$installer->endSetup(); 