<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('bannerads_blocks')};
CREATE TABLE {$this->getTable('bannerads_blocks')} (                                
 `block_id` int(11) unsigned NOT NULL auto_increment,
 `block_title` varchar(150) NOT NULL DEFAULT '',
 `block_position` varchar(128) NULL DEFAULT '',
 `display_type` varchar(128) NULL DEFAULT '',
 `from_date` date default '0000-00-00',
 `to_date` date default '0000-00-00', 
 `customer_group_ids` text NOT NULL,
 `sort_order` smallint(5) NULL DEFAULT 0,
 `status` smallint(6) NOT NULL default '0',
 `block_max_width` int(11) default '0',
 PRIMARY KEY (`block_id`)                                         
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Banner Block';
 
-- DROP TABLE IF EXISTS {$this->getTable('bannerads_images')};
CREATE TABLE {$this->getTable('bannerads_images')} (                                 
 `banner_id` int(11) unsigned NOT NULL auto_increment,  
 `banner_title` varchar(255) NOT NULL default '',               
 `banner_image` varchar(255) NOT NULL default '',          
 `banner_url` varchar(255) default NULL,                       
 `banner_description` text NOT NULL,    
 `sort_order` int(11) default '0',
 `status` smallint(6) NOT NULL default '0',              
 `created_time` datetime default NULL,                   
 `update_time` datetime default NULL, 
 PRIMARY KEY  (`banner_id`)                             
 ) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('bannerads_block_image_entity')};
CREATE TABLE {$this->getTable('bannerads_block_image_entity')} (
 `entity_id` int(11) unsigned NOT NULL auto_increment,
 `block_id` int(11) unsigned NOT NULL,
 `banner_id` int(11) unsigned NOT NULL,
 PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('bannerads_blocks_store')};
CREATE TABLE {$this->getTable('bannerads_blocks_store')} (                                
   `block_id` int(11)  NOT NULL ,
  `store_id` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('bannerads_reports')};
CREATE TABLE {$this->getTable('bannerads_reports')} (
  `report_id` int(11) unsigned NOT NULL auto_increment,
  `banner_id` int(11)  NULL,
  `block_id` int(11) NULL,
  `impression` int(11)  NULL default '0',
  `clicks` int(11)  NULL default '0',
  `date` datetime NULL,  
  PRIMARY KEY (`report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;    
");

$installer->endSetup(); 