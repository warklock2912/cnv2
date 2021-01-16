<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign_raffle_online` ADD `is_card_payment` BOOLEAN AFTER `point_spent`;
");
$installer->endSetup();
