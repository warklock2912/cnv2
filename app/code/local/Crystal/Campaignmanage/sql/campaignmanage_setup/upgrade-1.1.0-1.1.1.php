<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign` ADD `points_cost` INT unsigned DEFAULT NULL;
");
$installer->endSetup();
