<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Payrestriction
 */
$this->startSetup();
$this->run("
CREATE TABLE `{$this->getTable('ampayrestriction/rule')}` (
  `rule_id`     mediumint(8) unsigned NOT NULL auto_increment,
  `for_admin`   tinyint(1) unsigned NOT NULL default '0',
  `is_active`   tinyint(1) unsigned NOT NULL default '0',
  `all_stores`  tinyint(1) unsigned NOT NULL default '0',
  `all_groups`  tinyint(1) unsigned NOT NULL default '0',
  `name`        varchar(255) default '', 
  `stores`      varchar(255) NOT NULL default '', 
  `cust_groups` varchar(255) NOT NULL default '', 
  `message`     varchar(255) default '', 
  `methods`     text, 
  `conditions_serialized`   text, 
  PRIMARY KEY  (`rule_id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$this->endSetup();