<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoReviews
 */


class Amasty_SeoReviews_Block_Product_Reviews extends Mage_Core_Block_Text_List
{
	protected function _toHtml()
	{
		if (! Mage::helper('amseoreviews')->addReviewsToProductPage()) {
			return false;
		}

		return parent::_toHtml();
	}
}