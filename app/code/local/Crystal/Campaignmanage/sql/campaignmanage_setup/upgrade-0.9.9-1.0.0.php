<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign_raffle_online_products` MODIFY `options` VARCHAR(255);
");
$installer->endSetup();
