<?php
$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `imagehome` ADD `categories` varchar(255) NULL;

");

$installer->endSetup();
