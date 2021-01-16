<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */
$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('amseogooglesitemap/sitemap')}` CHANGE COLUMN `products_priority` `products_priority` VARCHAR(4) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
    ALTER TABLE `{$this->getTable('amseogooglesitemap/sitemap')}` CHANGE COLUMN `categories_priority` `categories_priority` VARCHAR(4) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
    ALTER TABLE `{$this->getTable('amseogooglesitemap/sitemap')}` CHANGE COLUMN `pages_priority` `pages_priority` VARCHAR(4) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
    ALTER TABLE `{$this->getTable('amseogooglesitemap/sitemap')}` CHANGE COLUMN `tags_priority` `tags_priority` VARCHAR(4) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
    ALTER TABLE `{$this->getTable('amseogooglesitemap/sitemap')}` CHANGE COLUMN `extra_priority` `extra_priority` VARCHAR(4) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
    ALTER TABLE `{$this->getTable('amseogooglesitemap/sitemap')}` CHANGE COLUMN `landing_priority` `landing_priority` VARCHAR(4) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
    ALTER TABLE `{$this->getTable('amseogooglesitemap/sitemap')}` CHANGE COLUMN `brands_priority` `brands_priority` VARCHAR(4) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
    ALTER TABLE `{$this->getTable('amseogooglesitemap/sitemap')}` ADD `categories_modified` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0';
");

$this->endSetup();