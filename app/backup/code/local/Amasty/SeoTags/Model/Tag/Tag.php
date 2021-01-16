<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoTags
 */

class Amasty_SeoTags_Model_Tag_Tag extends Mage_Tag_Model_Tag
{
	public function getTaggedProductsUrl()
	{
		/** @var Amasty_SeoTags_Helper_Data $helper */
		$helper = Mage::helper('amseotags');

		if ($helper->isTagRewritingEnabled()) {
			$uri     = Mage::getModel('catalog/product_url')->formatUrlKey($this->getName());
			$options = array();
			if ($this->getStoreId()) {
				$options['_store'] = $this->getStoreId();
			}

			$suffix = (string) Mage::getStoreConfig(Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_URL_SUFFIX);

			$url = Mage::getUrl('tag/' . $uri . '-' . $this->getId() . $suffix, $options);
			if (! empty($suffix)) {
				$url = preg_replace('/\/$/', '', $url);
			}

			return $url;
		} else {
			return parent::getTaggedProductsUrl();
		}
	}

	/**
	 * Save tag relation with product, customer and store
	 *
	 * @param $productId
	 * @param $customerId
	 * @param $storeId
	 * @param null $status
	 *
	 * @return string
	 */
	public function saveRelationRewritten($productId, $customerId, $storeId, $status = null)
	{
		/** @var $relationModel Mage_Tag_Model_Tag_Relation */
		$relationModel = Mage::getModel('tag/tag_relation');
		$relationModel->setTagId($this->getId())
			->setStoreId($storeId)
			->setProductId($productId)
			->setCustomerId($customerId)
			->setCreatedAt($relationModel->getResource()->formatDate(time()));

		$active = (int) ($status !== Mage_Tag_Model_Tag_Relation::STATUS_NOT_ACTIVE);
		$relationModel->setActive($active);

		$relationModelSaveNeed = false;
		switch($this->getStatus()) {
			case $this->getApprovedStatus():
				if($this->_checkLinkBetweenTagProduct($relationModel)) {
					$relation = $this->_getLinkBetweenTagCustomerProduct($relationModel);
					if ($relation->getId()) {
						if (!$relation->getActive()) {
							// activate relation if it was inactive
							$relationModel->setId($relation->getId());
							$relationModelSaveNeed = true;
						}
					} else {
						$relationModelSaveNeed = true;
					}
					$result = self::ADD_STATUS_EXIST;
				} else {
					$relationModelSaveNeed = true;
					$result = self::ADD_STATUS_SUCCESS;
				}
				break;
			case $this->getPendingStatus():
				$relation = $this->_getLinkBetweenTagCustomerProduct($relationModel);
				if ($relation->getId()) {
					if (!$relation->getActive()) {
						$relationModel->setId($relation->getId());
						$relationModelSaveNeed = true;
					}
				} else {
					$relationModelSaveNeed = true;
				}
				$result = self::ADD_STATUS_NEW;
				break;
			case $this->getDisabledStatus():
				if($this->_checkLinkBetweenTagCustomerProduct($relationModel)) {
					$result = self::ADD_STATUS_REJECTED;
				} else {
					$this->setStatus($this->getPendingStatus())->save();
					$relationModelSaveNeed = true;
					$result = self::ADD_STATUS_NEW;
				}
				break;
		}
		if ($relationModelSaveNeed) {
			$relationModel->save();
		}

		return $result;
	}

	public function getExportData()
	{
        $res = $this->getResource();
		$tablePrefix = Mage::getConfig()->getTablePrefix();
		$sql = "
			SELECT tr.store_id as store, tr.customer_id as customerID, pr.sku, pv.value as product_name, tr.active as is_active, GROUP_CONCAT(DISTINCT t.name) as product_tags
			FROM {$res->getTable('tag/relation')} tr
			right JOIN {$res->getTable('tag/tag')} t ON t.tag_id = tr.tag_id
			INNER JOIN {$res->getTable('catalog/product')} pr ON pr.entity_id = tr.product_id
			LEFT JOIN {$res->getTable('eav/attribute')} ea ON ea.entity_type_id = pr.entity_type_id AND ea.attribute_code = 'name'
			LEFT JOIN {$tablePrefix}{$res->getReadConnection()->getTableName('catalog_product_entity_varchar')} pv ON pv.attribute_id = ea.attribute_id AND pv.entity_id = pr.entity_id AND pv.store_id = 0
			GROUP BY tr.store_id, tr.product_id, tr.customer_id, tr.active
		";

		return $this->getResource()->getReadConnection()->fetchAll($sql);
	}

}
