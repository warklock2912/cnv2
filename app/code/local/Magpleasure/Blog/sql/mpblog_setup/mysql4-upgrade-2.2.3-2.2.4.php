<?php

$installer=$this;
$installer->startSetup();

$installer->run("
ALTER TABLE `mp_blog_categories` ADD `url_for_app` VARCHAR(255);
");

$installer->endSetup();
?>