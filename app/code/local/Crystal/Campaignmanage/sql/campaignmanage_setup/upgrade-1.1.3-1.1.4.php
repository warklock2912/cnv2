<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign_crop_and_drop` ADD `notification_id` INT AFTER `size`;
");
$installer->endSetup();
