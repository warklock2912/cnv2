<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign_subcribe_customer` ADD `is_showing` BOOLEAN ;
");
$installer->endSetup();
