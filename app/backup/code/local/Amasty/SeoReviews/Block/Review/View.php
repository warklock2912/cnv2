<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoReviews
 */


class Amasty_SeoReviews_Block_Review_View extends Mage_Review_Block_View
{
	/**
	 * Prepare link to review list for current product
	 *
	 * @return string
	 */
	public function getBackUrl()
	{
		return Mage::helper('amseotoolkit')->getProductUrl($this->getProductData());
	}
}
