<?php

$installer=$this;
$installer->startSetup();

$installer->run("
ALTER TABLE `mp_blog_categories` ADD `image_for_app` VARCHAR(255) NOT NULL;
ALTER TABLE `mp_blog_categories` ADD `category_for_app` TINYINT(1) ;
");

$installer->endSetup();
?>