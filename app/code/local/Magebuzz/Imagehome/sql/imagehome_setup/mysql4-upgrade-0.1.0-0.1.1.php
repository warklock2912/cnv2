<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('imagehome')};
CREATE TABLE {$this->getTable('imagehome')} (
  `imagehome_id` int(11) unsigned NOT NULL auto_increment,
  `imagehome_grid` text NOT NULL default '',
  `imagehome_image` text NOT NULL default '',
  `imagehome_link` text NOT NULL default '',
  PRIMARY KEY (`imagehome_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


    ");

$installer->endSetup(); 