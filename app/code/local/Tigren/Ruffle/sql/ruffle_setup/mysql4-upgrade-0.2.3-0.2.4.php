<?php
$installer = $this;
$installer->startSetup();

$installer->run("
	ALTER TABLE {$this->getTable('ruffle_joiner')} ADD `order_id` text NULL default ''; 
");

$installer->endSetup();