<?php

$installer=$this;
$installer->startSetup();

$installer->run("
ALTER TABLE `crystal_notification_device` ADD `device_id` VARCHAR(255);
");
$installer->endSetup();
?>