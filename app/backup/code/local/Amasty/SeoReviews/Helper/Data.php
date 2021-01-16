<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoReviews
 */


class Amasty_SeoReviews_Helper_Data extends Mage_Core_Helper_Abstract
{
	const HASH_TYPE_LIST = 'list';
	const HASH_TYPE_FORM = 'form';

	/**
	 * Redirect from review page to product
	 *
	 * @return mixed
	 */
	public function isRedirectToProductPageEnabled()
	{
		return Mage::getStoreConfig('amseoreviews/general/redirect_to_product');
	}

	/**
	 * Redirect from review page to product
	 *
	 * @return mixed
	 */
	public function addReviewsToProductPage()
	{
		return Mage::getStoreConfig('amseoreviews/additional/add_reviews_to_product');
	}

	/**
	 * Review list hash
	 *
	 * @return mixed
	 */
	public function getReviewListHash()
	{
		return trim(Mage::getStoreConfig('amseoreviews/additional/reviews_list_hash'));
	}

	/**
	 * Review form hash
	 *
	 * @return mixed
	 */
	public function getReviewFormHash()
	{
		return trim(Mage::getStoreConfig('amseoreviews/additional/review_form_hash'));
	}

	/**
	 * Get hash for url
	 *
	 * @param $hashType
	 *
	 * @return string
	 */
	public function getHashByType($hashType)
	{
		$hash = '';
		switch ($hashType) {
			case self::HASH_TYPE_LIST :
				$hash = $this->getReviewListHash();
				break;

			case self::HASH_TYPE_FORM :
				$hash = $this->getReviewFormHash();
				break;
		}

		return ! empty($hash) ? '#' . $hash : '';
	}

	/**
	 * @param Mage_Catalog_Model_Product $product
	 * @param string $hashType
	 *
	 * @return string
	 */
//	public function getReviewListUrl(Mage_Catalog_Model_Product $product, $hashType = '')
//	{
//		$hash = $this->getHashByType($hashType);
//		$url = Mage::helper('amseotoolkit')->getProductUrl($product);
//
//		return rtrim($url, '/') . $hash;
//	}

	/**
	 * @param Mage_Review_Model_Review $review
	 *
	 * @return string
	 */
	public function getReviewAlias(Mage_Review_Model_Review $review)
	{
		$value = Mage::getModel('catalog/product_url')->formatUrlKey($review->getTitle());
		$value = ! empty($value) ? $value : 'review';
		$value .= '-' . $review->getId();

		return $value;
	}

	/**
	 * @param $reviewId
	 * @param Mage_Catalog_Model_Product $product
	 * @param null $alias
	 *
	 * @return bool|mixed|string
	 */
	public function getReviewUrl($reviewId, Mage_Catalog_Model_Product $product = null, $alias = null)
	{
		$review = null;
		if ($alias === null || $product === null) {
			$review = Mage::getModel('review/review')->load($reviewId);
			if (! $review || ! $review->getId() || $review->getEntityId() != Mage_Review_Model_Review::ENTITY_PRODUCT) {
				return false;
			}
		}

		if ($alias === null) {
			$alias = $this->getReviewAlias($review);
		}

		if (! $product) {
			$product = Mage::getModel('catalog/product')->load($review->getEntityPkValue());
			if (! $product || ! $product->getId()) {
				return false;
			}
		}

		$suffix = (string) Mage::getStoreConfig(Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_URL_SUFFIX);

		$url = Mage::helper('amseotoolkit')->getProductUrl($product);
		$url = rtrim($url, '/');
		if (! empty($suffix)) {
			$url = rtrim($url, $suffix);
		}

		return $url . '/reviews/' . $alias . $suffix;
	}
}