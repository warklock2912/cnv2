<?php

$installer=$this;
$installer->startSetup();

$installer->run("
ALTER TABLE `crystal_notification` MODIFY  `type` VARCHAR(255) NOT NULL ;
");
$installer->endSetup();
