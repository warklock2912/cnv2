<?php
$installer = $this;
$installer->startSetup();

$installer->run("
	ALTER TABLE {$this->getTable('ruffle_joiner')} ADD `doc_invoice` text NULL default ''; 
");

$installer->endSetup();