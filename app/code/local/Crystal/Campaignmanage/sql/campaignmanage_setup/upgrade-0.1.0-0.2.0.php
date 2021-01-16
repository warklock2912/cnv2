<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign` ADD `content` TEXT;
");
$installer->endSetup();
