<?php
$installer = $this;
$installer->startSetup();

$installer->run("
  ALTER TABLE {$this->getTable('ruffle_joiner')} ADD `personal_id` text NULL default '';
");

$installer->endSetup();