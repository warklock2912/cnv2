<?php
$installer = $this;
$installer->startSetup();

$installer->run("
  ALTER TABLE shippop; 
");

$installer->endSetup();