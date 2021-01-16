<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign_raffle_online_subcrible` ADD `storepickup_id` INT AFTER `option`;
ALTER TABLE `campaign_raffle_online_subcrible` ADD `shipping_id` INT AFTER `storepickup_id`;
");
$installer->endSetup();
