<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign_raffle_online_subcrible` ADD `cc_card_token` VARCHAR(255) AFTER `shipping_id`;
");
$installer->endSetup();
