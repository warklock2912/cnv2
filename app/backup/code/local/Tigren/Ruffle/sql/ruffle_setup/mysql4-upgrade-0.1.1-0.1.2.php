<?php
$installer = $this;
$installer->startSetup();

$installer->run("
	ALTER TABLE {$this->getTable('ruffle_joiner')} ADD `is_winner` smallint(6) NULL default '0'; 
");

$installer->endSetup();