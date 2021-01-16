<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('bannerads_blocks')} CHANGE `from_date` `from_date` DATETIME NULL DEFAULT NULL;
ALTER TABLE {$this->getTable('bannerads_blocks')} CHANGE `to_date` `to_date` DATETIME NULL DEFAULT NULL;

");

$installer->endSetup();
