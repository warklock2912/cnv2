<?php

$installer=$this;
$installer->startSetup();

$installer->run("
ALTER TABLE `mp_blog_posts` ADD  `is_sent` BOOLEAN;
");
$installer->endSetup();
