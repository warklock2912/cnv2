<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `crystal_notification` ADD `title` VARCHAR(255);
ALTER TABLE `crystal_notification` ADD `url` VARCHAR(255);
ALTER TABLE `crystal_notification` DROP COLUMN  `read`;
ALTER TABLE `crystal_notification` DROP COLUMN  `image`;
");
$installer->endSetup();
