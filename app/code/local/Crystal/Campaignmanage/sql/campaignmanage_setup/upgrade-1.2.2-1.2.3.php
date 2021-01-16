<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign_raffle_online` ADD `stores_active` VARCHAR(255)  AFTER `allow_pickup`;
ALTER TABLE `campaign_raffle_online` ADD `allow_shipping` BOOLEAN  AFTER `point_spent`;
");
$installer->endSetup();
