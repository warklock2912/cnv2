<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoSingleUrl
 */

class Amasty_SeoSingleUrl_Model_Config
{
	const PRODUCT_URL_PATH_SHORTEST = 1;
	const PRODUCT_URL_PATH_LONGEST = 2;

	public function getProductUrlType()
	{
		return Mage::getStoreConfig('amseourl/general/product_url_type');
	}

}