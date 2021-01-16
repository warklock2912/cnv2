<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoReviews
 */


class Amasty_SeoReviews_Block_Review_Product_View_List extends Mage_Review_Block_Product_View_List
{
	protected $_reviewAliases;

	public function getReviewUrl($id)
	{
		$aliases = $this->_getReviewAliases();
		if (! empty($aliases[$id])) {
			return Mage::helper('amseoreviews')->getReviewUrl($id, $this->getProduct(), $aliases[$id]);
		} else {
			return Mage::getUrl('*/*/view', array('id' => $id));
		}
	}

	protected function _getReviewAliases()
	{
		if (! $this->_reviewAliases) {
			foreach ($this->getReviewsCollection() as $item) {
				$this->_reviewAliases[$item->getId()] = Mage::helper('amseoreviews')->getReviewAlias($item);
			}
		}

		return $this->_reviewAliases;
	}

}
