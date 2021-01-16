<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `crystal_notification_list` ADD `title` VARCHAR (255) AFTER `content_id`;
ALTER TABLE `crystal_notification_list` ADD `short_content` VARCHAR (255) AFTER `title`;
");
$installer->endSetup();
