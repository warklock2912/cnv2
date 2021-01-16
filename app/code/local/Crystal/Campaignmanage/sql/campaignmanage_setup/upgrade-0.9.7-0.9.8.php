<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign_raffle_online` ADD `content` TEXT ;
");
$installer->endSetup();
