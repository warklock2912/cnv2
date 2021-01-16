<?php

$installer=$this;
$installer->startSetup();

$installer->run("
ALTER TABLE `customer_entity` ADD COLUMN `m_token` VARCHAR(64)  ;
");
$installer->endSetup();
