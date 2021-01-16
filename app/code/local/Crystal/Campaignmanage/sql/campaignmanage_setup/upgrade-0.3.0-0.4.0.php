<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign_subcribe_customer` ADD `no_of_id` INTEGER ;
");
$installer->endSetup();
