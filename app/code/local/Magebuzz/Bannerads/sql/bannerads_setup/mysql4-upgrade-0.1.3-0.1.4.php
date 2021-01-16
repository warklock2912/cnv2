<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('bannerads_images')} ADD `target` smallint(6) NULL default 0;
ALTER TABLE {$this->getTable('bannerads_images')} ADD `start_time` datetime NULL;
ALTER TABLE {$this->getTable('bannerads_images')} ADD `end_time` datetime NULL;
 
");

$installer->endSetup(); 