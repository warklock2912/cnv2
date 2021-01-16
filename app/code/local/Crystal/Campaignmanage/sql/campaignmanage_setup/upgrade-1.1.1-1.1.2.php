<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign_raffle_online` ADD `point_spent` INT AFTER `content`;
");
$installer->endSetup();
