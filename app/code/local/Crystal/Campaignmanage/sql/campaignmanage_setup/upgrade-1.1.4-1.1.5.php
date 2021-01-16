<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign` ADD `is_end` INT ;
");
$installer->endSetup();
