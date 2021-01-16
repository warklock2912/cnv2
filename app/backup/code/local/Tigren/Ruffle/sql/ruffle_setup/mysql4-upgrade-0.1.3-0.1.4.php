<?php
$installer = $this;
$installer->startSetup();

$installer->run("
  ALTER TABLE {$this->getTable('ruffle_joiner')} ADD `pdf_path` text NULL default '';
");

$installer->endSetup();