<?php
$installer = $this;
$installer->startSetup();

$installer->run("
  ALTER TABLE {$this->getTable('ruffle_joiner')} ADD `customer_card_token` varchar(550); 
");

$installer->endSetup();
