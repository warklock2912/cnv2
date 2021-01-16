<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign` ADD `is_waiting` BOOLEAN;
");
$installer->endSetup();
