<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('bannerads_blocks')} ADD `category` varchar(255) NULL;
ALTER TABLE {$this->getTable('bannerads_blocks')} ADD `category_type` smallint(4) NOT NULL DEFAULT '0';
 
");

$installer->endSetup(); 