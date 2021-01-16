<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `crystal_notification` ADD `is_sent` BOOLEAN;
");
$installer->endSetup();
