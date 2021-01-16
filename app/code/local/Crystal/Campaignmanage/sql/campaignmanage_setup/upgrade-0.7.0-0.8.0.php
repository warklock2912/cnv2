<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign_products` ADD `option` VARCHAR(255) ;
");
$installer->endSetup();
