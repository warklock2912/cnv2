<?php

$installer=$this;
$installer->startSetup();

$installer->run("
ALTER TABLE `crystal_notification_list` ADD  `content_args` VARCHAR(255)  AFTER `short_content`;
ALTER TABLE `crystal_notification` ADD  `message_args` VARCHAR(255) AFTER `message`;
");
$installer->endSetup();
