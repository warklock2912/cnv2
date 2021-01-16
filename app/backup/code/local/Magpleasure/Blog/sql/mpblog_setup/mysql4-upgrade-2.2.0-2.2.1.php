<?php

$installer=$this;
$installer->startSetup();

$installer->run("
ALTER TABLE `mp_blog_categories` MODIFY COLUMN `description` TEXT;
");

$installer->endSetup();
?>