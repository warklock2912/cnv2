<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoReviews
 */


class Amasty_SeoReviews_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard
{
/*    public function __construct(){
        if(!Mage::getStoreConfig('amseoreviews/additional/add_reviews_to_product')){
            return false;
        }
    }*/

	/**
	 * @param Zend_Controller_Request_Http $request
	 *
	 * @return bool
	 */
	public function match(Zend_Controller_Request_Http $request)
	{
        //  product-url-key/reviews/alias-115.html
        //  product-url-key/reviews/alias-115

        $isContIndex = $isContLocale = '';
        $identifier = trim($request->getPathInfo(), '/');
		$parts      = explode('/', $identifier);

        if (count($parts) < 3) {
            return false;
        }

        $alias      = array_pop($parts);
        $isReview   = array_pop($parts);
        $productUrlKey = array_pop($parts);

        if ($isReview != 'reviews') {
            return false;
        }

        $reviewId = explode('-', $alias);
        $reviewId = intval(array_pop($reviewId)); // intval converts 115.html to just 115
        if (! $reviewId) {
            return false;
        }

        /** @var Amasty_SeoReviews_Helper_Data $helper */
        $helper = Mage::helper('amseoreviews');
        if ($helper->isRedirectToProductPageEnabled()){
            $product    = Mage::getModel('catalog/product')->loadByAttribute('url_key', $isContIndex . $isContLocale . $productUrlKey);
            if (!$product && !$product->getId()) {
                return false;
            }

            $hash = $helper->getHashByType('list');
            $url = rtrim(Mage::helper('amseotoolkit')->getProductUrl($product), '/') . $hash;

            Mage::app()->getResponse()
                ->setRedirect($url, 301)
                ->sendResponse();
            exit;
        }

        $request->setRouteName('review')
            ->setModuleName('review')
            ->setControllerName('product')
            ->setActionName('view')
            ->setParam('id', $reviewId)
            ->setAlias(
                Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
                'review'
        );

		return true;
	}
}