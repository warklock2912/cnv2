<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign_raffle_online_subcrible` ADD `order_id` VARCHAR(255) AFTER `cc_card_token`;
ALTER TABLE `campaign_raffle_online_subcrible` ADD `shipping_method` VARCHAR(255) AFTER `order_id`;
");
$installer->endSetup();
