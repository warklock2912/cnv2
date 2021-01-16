<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `p2c2p_token` ADD `expired_month` VARCHAR(64) AFTER `card_type`;
ALTER TABLE `p2c2p_token` ADD `expired_year` VARCHAR(64) AFTER `expired_month`;
ALTER TABLE `p2c2p_token` ADD `transaction_id` VARCHAR(255) AFTER `expired_year`;
");
$installer->endSetup();
