<?php
$installer = $this;
$installer->startSetup();

$installer->run("
	ALTER TABLE {$this->getTable('ruffle')} ADD `is_invoice` int(1); 
");

$installer->endSetup();