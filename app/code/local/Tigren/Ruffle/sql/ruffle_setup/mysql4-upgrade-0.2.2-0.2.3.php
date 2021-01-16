<?php
$installer = $this;
$installer->startSetup();

$installer->run("
	ALTER TABLE {$this->getTable('ruffle')} ADD `use_creditcard` int(1) DEFAULT 0; 
ALTER TABLE {$this->getTable('ruffle_joiner')} ADD COLUMN `send_email` datetime NULL DEFAULT NULL;
");

$installer->endSetup();
