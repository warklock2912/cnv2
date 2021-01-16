<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign_raffle_online` ADD `image` VARCHAR(255);
");
$installer->endSetup();
