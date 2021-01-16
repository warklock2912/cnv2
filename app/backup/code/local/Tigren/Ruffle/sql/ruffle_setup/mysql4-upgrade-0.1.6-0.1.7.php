<?php
$installer = $this;
$installer->startSetup();

$installer->run("
  ALTER TABLE {$this->getTable('ruffle_joiner')} ADD `msg` text NULL default '';
");

$installer->endSetup();