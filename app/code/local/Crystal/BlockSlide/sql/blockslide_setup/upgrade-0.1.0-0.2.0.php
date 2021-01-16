<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `crystal_block_slide` ADD `status` BOOLEAN;
");
$installer->endSetup();
