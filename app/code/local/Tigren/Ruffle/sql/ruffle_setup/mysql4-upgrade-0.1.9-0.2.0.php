<?php
$installer = $this;
$installer->startSetup();

$installer->run("
  ALTER TABLE {$this->getTable('ruffle')} ADD `is_pickup` int(1) NULL ;
  ALTER TABLE {$this->getTable('ruffle_joiner')} ADD `customer_ruffle_address` varchar(250) NULL ;
");

$installer->endSetup();
