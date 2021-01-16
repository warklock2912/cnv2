<?php

$installer=$this;
$installer->startSetup();

$installer->run("
ALTER TABLE `mp_blog_posts` MODIFY COLUMN `is_sent` BOOLEAN NOT NULL ;
");
$installer->endSetup();
