<?php

$installer=$this;
$installer->startSetup();

$installer->run("
ALTER TABLE `crystal_notification_list` ADD COLUMN `is_card_payment` BOOLEAN  ;
");
$installer->endSetup();
