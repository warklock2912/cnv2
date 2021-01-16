<?php
$installer = $this;
$installer->startSetup();

$installer->run("
  ALTER TABLE {$this->getTable('ruffle')} ADD `is_all` int(1) NULL ;
");

$installer->endSetup();