<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */
$installer = $this;
$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('blog_images')};
CREATE TABLE {$this->getTable('blog_images')} (
  `blog_images_id` int(10) unsigned NOT NULL auto_increment,
  `post_id` int(11),
  `images` text NULL,
  PRIMARY KEY (`blog_images_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();
