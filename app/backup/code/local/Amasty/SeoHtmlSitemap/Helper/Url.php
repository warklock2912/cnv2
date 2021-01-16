<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoHtmlSitemap
 */


class Amasty_SeoHtmlSitemap_Helper_Url extends Mage_Core_Helper_Abstract
{
	public function getSitemapUrl($includeBase = true)
	{
		$suffix = (string) Mage::getStoreConfig(Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_URL_SUFFIX);
        if (strlen($suffix) > 1 && strpos($suffix, '.') === false) {
            $suffix = '.' . $suffix;
        }

		$baseUrl = $includeBase !== false ? Mage::getBaseUrl() : '';
		$siteMapFrontendUrl = trim((string) Mage::getStoreConfig(Amasty_SeoHtmlSitemap_Helper_Data::CONFIG_URL));

		return $baseUrl . $siteMapFrontendUrl . trim($suffix);
	}
}