<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoSingleUrl
 */

class Amasty_SeoSingleUrl_Helper_Data extends Mage_Core_Helper_Abstract
{
	const PRODUCT_URL_PATH_DEFAULT  = 0;
	const PRODUCT_URL_PATH_SHORTEST = 1;
	const PRODUCT_URL_PATH_LONGEST  = 2;

	/**
	 * @return bool
	 */
	public static function urlRewriteHelperEnabled()
	{
		return version_compare(Mage::getVersion(), '1.8') >= 0;
	}

	/**
	 * Product url type (shortest/longest/default)
	 *
	 * @return mixed
	 */
	public function getProductUrlType()
	{
		return Mage::getStoreConfig('amseourl/general/product_url_type');
	}

	/**
	 * @return bool
	 */
	public function useDefaultProductUrlRules()
	{
		return (int) $this->getProductUrlType() == self::PRODUCT_URL_PATH_DEFAULT
			   || ! Mage::getStoreConfig(Mage_Catalog_Helper_Product::XML_PATH_PRODUCT_URL_USE_CATEGORY);
	}
}