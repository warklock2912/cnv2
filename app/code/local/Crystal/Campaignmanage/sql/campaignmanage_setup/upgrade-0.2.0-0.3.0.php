<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign` ADD `image` VARCHAR(64);
");
$installer->endSetup();
