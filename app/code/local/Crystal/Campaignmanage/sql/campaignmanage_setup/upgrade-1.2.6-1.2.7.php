<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign_raffle_online_subcrible` ADD `send_email` DATETIME AFTER `assigned_winner`;
");
$installer->endSetup();
