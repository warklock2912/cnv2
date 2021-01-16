<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign_raffle_online_subcrible` ADD `2c2p_status` BOOLEAN AFTER `order_id`;
");
$installer->endSetup();
