<?php

$installer=$this;
$installer->startSetup();

$installer->run("
ALTER TABLE `crystal_notification` ADD `type` VARCHAR(255) AFTER `url`;
");
$installer->endSetup();
