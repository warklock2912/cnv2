<?php
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('directory_region_city_name')};
CREATE TABLE {$this->getTable('directory_region_city_name')} (  
  `locale` varchar(8) NOT NULL,
	`city_id` int(11) unsigned NOT NULL DEFAULT '0',
	`name` varchar(255) NULL DEFAULT NULL,  
  PRIMARY KEY (`locale`, `city_id`),
	CONSTRAINT `FK_DIRECTORY_REGION_CITY_NAME_DIRECTORY_REGION_CITY` FOREIGN KEY (`city_id`) REFERENCES `{$this->getTable('directory_region_city')}` (`city_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('directory_city_subdistrict_name')};
CREATE TABLE {$this->getTable('directory_city_subdistrict_name')} (
  `locale` varchar(8) NOT NULL,
	`subdistrict_id` int(11) unsigned NOT NULL DEFAULT '0',  
	`name` varchar(255) NULL DEFAULT NULL,  	
  PRIMARY KEY (`locale`, `subdistrict_id`),
	CONSTRAINT `FK_DIRECTORY_CITY_SUBDISTRICT_NAME_DIRECTORY_CITY_SUBDISTRICT` FOREIGN KEY (`subdistrict_id`) REFERENCES `{$this->getTable('directory_city_subdistrict')}` (`subdistrict_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");
		
$installer->endSetup();