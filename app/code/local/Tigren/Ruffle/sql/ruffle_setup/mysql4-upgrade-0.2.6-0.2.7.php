<?php
$installer = $this;
$installer->startSetup();

$installer->run("
  ALTER TABLE {$this->getTable('ruffle_joiner')} ADD `firstname` varchar(550); 
  ALTER TABLE {$this->getTable('ruffle_joiner')} ADD `lastname` varchar(550); 
");

$installer->endSetup();
