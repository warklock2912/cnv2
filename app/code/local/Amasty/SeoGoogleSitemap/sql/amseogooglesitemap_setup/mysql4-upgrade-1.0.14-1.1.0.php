<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */
$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('amseogooglesitemap/sitemap')}` ADD `blog` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0';
    ALTER TABLE `{$this->getTable('amseogooglesitemap/sitemap')}` ADD `blog_priority` VARCHAR (4) NOT NULL DEFAULT '';
    ALTER TABLE `{$this->getTable('amseogooglesitemap/sitemap')}` ADD `blog_frequency` VARCHAR (16) NOT NULL DEFAULT '';
");

$this->endSetup();