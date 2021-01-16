<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('directory_region_city')};
CREATE TABLE {$this->getTable('directory_region_city')} (
  `city_id` int(11) unsigned NOT NULL auto_increment,
  `code` varchar(32) NOT NULL,
	`default_name` varchar(255) NOT NULL,  
  `region_id` int(11) unsigned NULL,
	`sync_id` varchar(10) NULL default NULL,
  PRIMARY KEY (`city_id`),
	CONSTRAINT `FK_DIRECTORY_REGION_CITY_DIRECTORY_COUNTRY_REGION` FOREIGN KEY (`region_id`) REFERENCES `{$this->getTable('directory_country_region')}` (`region_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('directory_city_subdistrict')};
CREATE TABLE {$this->getTable('directory_city_subdistrict')} (
  `subdistrict_id` int(11) unsigned NOT NULL auto_increment,
  `code` varchar(32) NOT NULL,
	`default_name` varchar(255) NOT NULL,  
	`zipcode` varchar(255) DEFAULT NULL,
  `city_id` int(11) unsigned NULL,	
	`sync_id` varchar(10) NULL default NULL,
  PRIMARY KEY (`subdistrict_id`),
	CONSTRAINT `FK_DIRECTORY_CITY_SUBDISTRICT_DIRECTORY_REGION_CITY` FOREIGN KEY (`city_id`) REFERENCES `{$this->getTable('directory_region_city')}` (`city_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE {$this->getTable('directory_country_region')} ADD `sync_id` VARCHAR(10) NULL;

    ");

$installer->endSetup(); 