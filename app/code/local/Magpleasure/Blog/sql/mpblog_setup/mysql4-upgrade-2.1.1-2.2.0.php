<?php

$installer=$this;
$installer->startSetup();

$installer->run("
ALTER TABLE `mp_blog_categories` ADD `description` VARCHAR(255) NOT NULL;
");

$installer->endSetup();
?>