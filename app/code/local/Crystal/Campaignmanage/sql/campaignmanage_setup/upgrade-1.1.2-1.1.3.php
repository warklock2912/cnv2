<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign_raffle_online` ADD `status` INT AFTER `image`;
");
$installer->endSetup();
