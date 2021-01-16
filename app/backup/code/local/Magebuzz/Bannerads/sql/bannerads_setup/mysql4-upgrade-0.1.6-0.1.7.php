<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('bannerads_images')} ADD `banner_show_desc` smallint(6) NOT NULL default '0';
ALTER TABLE {$this->getTable('bannerads_images')} ADD `banner_description_pos` varchar(128) NULL DEFAULT '';
 
");

$installer->endSetup(); 