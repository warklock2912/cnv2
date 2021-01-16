<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign_raffle_online_subcrible` ADD `assigned_loser` BOOLEAN AFTER `is_winner`;
");
$installer->endSetup();
