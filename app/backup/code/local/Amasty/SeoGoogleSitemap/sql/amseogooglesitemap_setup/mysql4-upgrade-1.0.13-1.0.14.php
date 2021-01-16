<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */
$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('amseogooglesitemap/sitemap')}` ADD `exclude_urls` TEXT NOT NULL;
");

$this->endSetup();