<?php
$installer = $this;
$installer->startSetup();

$installer->run("
  ALTER TABLE {$this->getTable('ruffle')} ADD `email_join_th` text NULL ;
  ALTER TABLE {$this->getTable('ruffle')} ADD `email_join_en` text NULL ;
");

$installer->endSetup();