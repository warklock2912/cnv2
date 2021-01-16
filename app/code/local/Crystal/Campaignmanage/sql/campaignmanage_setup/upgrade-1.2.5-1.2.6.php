<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign_raffle_online_subcrible` ADD `assigned_winner` BOOLEAN AFTER `assigned_loser`;
");
$installer->endSetup();
