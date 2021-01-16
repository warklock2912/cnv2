<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `crystal_block_slide` ADD `url` VARCHAR(255);
");
$installer->endSetup();
