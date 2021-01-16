<?php
$installer = $this;
$installer->startSetup();

$installer->run("
  ALTER TABLE {$this->getTable('ruffle')} ADD `available_day` text NULL default '';
");

$installer->endSetup();