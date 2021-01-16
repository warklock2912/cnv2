<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoSingleUrl
 */


class Amasty_SeoSingleUrl_Model_Resource_Product_Link_Product_Collection extends Mage_Catalog_Model_Resource_Product_Link_Product_Collection
{
	protected function _addUrlRewrite()
	{
		/** @var Amasty_SeoSingleUrl_Helper_Data $helper */
		$helper = Mage::helper('amseourl');
		if (Amasty_SeoSingleUrl_Helper_Data::urlRewriteHelperEnabled() || $helper->useDefaultProductUrlRules()) {
			parent::_addUrlRewrite();
		} else {
			$urlRewrites = null;
			if ($this->_cacheConf) {
				if (! ($urlRewrites = Mage::app()->loadCache($this->_cacheConf['prefix'] . 'urlrewrite'))) {
					$urlRewrites = null;
				} else {
					$urlRewrites = unserialize($urlRewrites);
				}
			}

			if (! $urlRewrites) {
				$productIds = array();
				foreach ($this->getItems() as $item) {
					$productIds[] = $item->getEntityId();
				}
				if (! count($productIds)) {
					return;
				}

				/** @var Amasty_SeoSingleUrl_Helper_Product_Url_Rewrite $helper */
				$helper  = Mage::helper('amseourl/product_url_rewrite');
				$storeId = $this->getStoreId() ? $this->getStoreId() : Mage::app()->getStore()->getId();
				$select  = $helper
					->getTableSelect($productIds, $this->_urlRewriteCategory, $storeId);

				foreach ($this->getConnection()->fetchAll($select) as $row) {
					if (! isset($urlRewrites[$row['product_id']])) {
						$urlRewrites[$row['product_id']] = $row['request_path'];
					}
				}

				if ($this->_cacheConf) {
					Mage::app()->saveCache(
						serialize($urlRewrites),
						$this->_cacheConf['prefix'] . 'urlrewrite',
						array_merge($this->_cacheConf['tags'], array(Mage_Catalog_Model_Product_Url::CACHE_TAG)),
						$this->_cacheLifetime
					);
				}
			}

			foreach ($this->getItems() as $item) {
				if (isset($urlRewrites[$item->getEntityId()])) {
					$item->setData('request_path', $urlRewrites[$item->getEntityId()]);
				} else {
					$item->setData('request_path', false);
				}
			}
		}
	}
}
