<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `crystal_notification_list` ADD `product_id` VARCHAR (255) AFTER `short_content`;
ALTER TABLE `crystal_notification_list` ADD `selected_size` VARCHAR (255) AFTER `product_id`;
");
$installer->endSetup();
