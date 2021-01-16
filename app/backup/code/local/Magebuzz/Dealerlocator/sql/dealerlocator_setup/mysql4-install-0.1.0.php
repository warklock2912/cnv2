<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('dealerlocator')};
CREATE TABLE {$this->getTable('dealerlocator')} (
  `dealerlocator_id` int(11) unsigned NOT NULL auto_increment,
  `title` text NOT NULL default '',
  `email` text NOT NULL default '',
  `phone` text NOT NULL default '',
  `website` text NOT NULL default '',
  `postal_code` text NOT NULL default '',
  `address` text NOT NULL default '',
  `longitude` text NOT NULL default '',
  `latitude` text NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `note` text NOT NULL default '',
  PRIMARY KEY (`dealerlocator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 