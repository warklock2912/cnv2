<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoReviews
 */


class Amasty_SeoReviews_Model_Observer
{
	public function initControllerRouters(Varien_Event_Observer $observer)
	{
		$front   = $observer->getFront();
		$request = $front->getRequest();
		$parts   = explode('/', trim($request->getPathInfo(), '/'));

		$this->_checkReviewListPage($parts, $front);
		$this->_checkReviewItemPage($parts, $front);
	}

	/**
	 * Check for review item page
	 *
	 * @param $parts
	 * @param $front
	 */
	protected function _checkReviewItemPage($parts, $front)
	{
		/** @var Amasty_SeoReviews_Helper_Data $helper */
		$helper = Mage::helper('amseoreviews');
        if(count($parts) > 5){
            $parts = array_slice($parts, -5);
        }
		if (count($parts) == 5) {
			$reviewId = array_pop($parts);
			if ((int) $reviewId > 0 && array('review', 'product', 'view', 'id') == $parts) {
				$default = new Mage_Core_Controller_Varien_Router_Default();
				$front->addRouter('default', $default);

				if ($url = $helper->getReviewUrl($reviewId)) {
					Mage::app()->getResponse()
						->setRedirect($url, 301)
						->sendResponse();

					exit;
				}
			}
		}
	}

	/**
	 * check for review list page
	 *
	 * @param $parts
	 * @param $front
	 */
	protected function _checkReviewListPage($parts, $front)
	{
        // product-url-key/reviews/alias-115.html                               3
        // product-url-key/reviews/alias-115                                    3
        // review/product/view/id/115/                                          5
        // review/product/list/id/132/category/12/                              7

		/** @var Amasty_SeoReviews_Helper_Data $helper */
		$helper = Mage::helper('amseoreviews');
        //if category param was passed        
        if (count($parts) == 7) {
            array_pop($parts);
            array_pop($parts);
        }

        $productId = array_pop($parts);
        $isMatched = (int) $productId > 0 && array('review', 'product', 'list', 'id') == $parts;

        if ($isMatched) {
            if ($productId) {
                $product = Mage::getModel('catalog/product')
                ->load($productId);
            }

            if ($product->getId()) {
                $hash = $helper->getHashByType('list');
                $url = rtrim(Mage::helper('amseotoolkit')->getProductUrl($product), '/') . $hash;

                Mage::app()->getResponse()
                    ->setRedirect($url, 301)
                    ->sendResponse();
                exit;
            }
	    }
	}
}
