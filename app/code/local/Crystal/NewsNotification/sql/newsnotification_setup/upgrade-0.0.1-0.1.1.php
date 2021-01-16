<?php

$installer=$this;
$installer->startSetup();

$installer->run("
ALTER TABLE `crystal_newsnotification` ADD `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT;
");
$installer->endSetup();
?>