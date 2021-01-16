<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('bannerads_images')} ADD `selectbanners` smallint(6) NULL default 0;
ALTER TABLE {$this->getTable('bannerads_images')} ADD `video_url` text ; 
");

$installer->endSetup();
