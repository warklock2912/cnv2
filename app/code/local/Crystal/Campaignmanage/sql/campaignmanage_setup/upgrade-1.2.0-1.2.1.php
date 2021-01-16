<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign_raffle_online` ADD `allow_pickup` BOOLEAN AFTER `point_spent`;
ALTER TABLE `campaign_raffle_online` ADD `am_table_method_id` VARCHAR(255) AFTER `point_spent`;
");
$installer->endSetup();
