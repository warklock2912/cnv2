<?php
$installer = $this;
$installer->startSetup();

$installer->run("
  ALTER TABLE {$this->getTable('ruffle_joiner')} ADD `is_savecard` smallint(1) NULL DEFAULT 0;
");

$installer->endSetup();
