<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Meta
 */


class Amasty_Meta_Helper_UrlKeyHandler extends Mage_Core_Helper_Abstract
{
	protected $_connection;
	protected $_tablePrefix;
	protected $_productTypeId;
	protected $_urlPathId;
	protected $_urlKeyId;
    protected $_pageSize = 100;

    /**
     * Base product target path.
     */
    const BASE_PRODUCT_TARGET_PATH  = 'catalog/product/view/id/%d';
    /**
     * Base path for product in category
     */
    const BASE_PRODUCT_CATEGORY_TARGET_PATH = 'catalog/product/view/id/%d/category/%d';


    public function __construct()
	{
		//connection
		$this->_connection = Mage::getSingleton('core/resource')->getConnection('core_write');

		//table prefix
		$this->_tablePrefix = (string) Mage::getConfig()->getTablePrefix();

		//product type id
		$select               = $this->_connection->select()->from($this->_tablePrefix . 'eav_entity_type')
			->where("entity_type_code = 'catalog_product'");
		$this->_productTypeId = $this->_connection->fetchOne($select);

		//url path id
		$select           = $this->_connection->select()->from($this->_tablePrefix . 'eav_attribute')
			->where("entity_type_id = $this->_productTypeId AND (attribute_code = 'url_path')");
		$this->_urlPathId = $this->_connection->fetchOne($select);

		//url key id
		$select          = $this->_connection->select()->from($this->_tablePrefix . 'eav_attribute')
			->where("entity_type_id = $this->_productTypeId AND (attribute_code = 'url_key')");
		$this->_urlKeyId = $this->_connection->fetchOne($select);
	}

    public function isEnterprise()
    {
        if (!method_exists('Mage', 'getEdition'))
            return false;

        return Mage::getEdition() == Mage::EDITION_ENTERPRISE;
    }

	/**
	 * @param $urlKeyTemplate
	 * @param array $storeIds
     * @param int $page
	 */
	public function process($urlKeyTemplate, $storeIds = array(), $page = 1)
	{
        $storeEntities = $this->_getStores($storeIds);

		foreach ($storeEntities as $store) {
            $products = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('*')
                ->setCurPage($page)
                ->setPageSize($this->getPageSize())
                ->setStore($store);

            foreach ($products as $product) {
                $this->processProduct($product, $store, $urlKeyTemplate);

                if ($this->isEnterprise()) {
                    if ($store->getId() == Mage_Core_Model_App::ADMIN_STORE_ID) {
                        $rewriteStoreIds = $product->getStoreIds();
                    }
                    else {
                        $rewriteStoreIds = array($product->getStoreId());
                    }

                    foreach ($rewriteStoreIds as $storeId){
                        $this->updateEnterpriseRewrite($product, $storeId);
                        foreach ($product->getCategoryIds() as $categoryId) {
                            $this->updateEnterpriseRewrite($product, $storeId, $categoryId);
                        }
                    }
                }
            }
		}
	}

    public function estimate($storeIds = array())
    {
        $products = Mage::getModel('catalog/product')->getCollection();

        if ($storeIds){
            $products->setStore($storeIds[0]);
        }

        return $products->getSize();
    }

    protected function _getStores($storeIds)
    {
        $storeEntities = Mage::app()->getStores(true, true);
        if (! empty($storeIds)) {
            foreach ($storeEntities as $key => $storeEntity) {
                if (! in_array($key, $storeIds)) {
                    unset($storeEntities[$key]);
                }
            }
        }

        return $storeEntities;
    }

	public function processProduct(Mage_Catalog_Model_Product $product, $store, $urlKeyTemplate = '')
	{
		if (empty($urlKeyTemplate)) {
			$urlKeyTemplate = trim(Mage::getStoreConfig('ammeta/product/url_template', $store));
		}

		if (empty($urlKeyTemplate)) {
			return;
		}

		/** @var Amasty_Meta_Helper_Data $helper */
		$helper = Mage::helper('ammeta');

		$product->setStoreId($store->getId());
		$urlKey = $helper->cleanEntityToCollection()
			->addEntityToCollection($product)
			->parse($urlKeyTemplate, true);

		$urlKey = $product->formatUrlKey($urlKey);

		//update url_key
		$this->_updateUrlKey($product, $store->getId(), $urlKey);
		//update url_path
		$this->_updateUrlPath($product, $store->getId(), $urlKey,
			Mage::getStoreConfig('catalog/seo/product_url_suffix', $store));

		$product->setUrlKey($urlKey);
	}

	/**
	 * @param $product
	 * @param $storeId
	 * @param $urlKey
	 * @param string $urlSuffix
	 */
	protected function _updateUrlKey($product, $storeId, $urlKey, $urlSuffix = '')
	{
		$this->_updateAttribute($this->_urlKeyId, $product, $storeId, $urlKey, $urlSuffix);
	}

	/**
	 * @param $product
	 * @param $storeId
	 * @param $urlKey
	 * @param string $urlSuffix
	 */
	protected function _updateUrlPath($product, $storeId, $urlKey, $urlSuffix = '')
	{
		$this->_updateAttribute($this->_urlPathId, $product, $storeId, $urlKey, $urlSuffix);
	}

	/**
	 * @param $attributeId
	 * @param $product
	 * @param $storeId
	 * @param $urlKey
	 * @param $urlSuffix
	 */
	protected function _updateAttribute($attributeId, $product, $storeId, $urlKey, $urlSuffix)
	{
        if ($this->isEnterprise()) {
            $table = 'catalog_product_entity_url_key';
        }
        else {
            $table = 'catalog_product_entity_varchar';
        }

		$select = $this->_connection->select()->from($this->_tablePrefix . $table)
			->where("entity_type_id = $this->_productTypeId AND attribute_id = $this->_urlKeyId AND entity_id = {$product->getId()} AND store_id = {$storeId}");
		$row    = $this->_connection->fetchRow($select);

		if ($row) {
			$this->_connection->update($this->_tablePrefix . $table,
				array('value' => $urlKey . $urlSuffix),
				"entity_type_id = $this->_productTypeId AND attribute_id = $attributeId AND entity_id = {$product->getId()} AND store_id = {$storeId}");
		} else {
			$data = array(
				'entity_type_id' => $this->_productTypeId,
				'attribute_id'   => $attributeId,
				'entity_id'      => $product->getId(),
				'store_id'       => $storeId,
				'value'          => $urlKey . $urlSuffix
			);
			$this->_connection->insert($this->_tablePrefix . $table, $data);
		}
	}

    public function getPageSize()
    {
        return $this->_pageSize;
    }

    /**
     * Create custom redirect for product in store and category
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int $storeId
     * @param int|null $categoryId
     */
    public function updateEnterpriseRewrite($product, $storeId, $categoryId = null)
    {
        /** @var $helper Enterprise_Catalog_Helper_Data */
        $helper = Mage::helper('enterprise_catalog');

        $requestPath = $helper->getProductRequestPath($product->getRequestPath(), $storeId, $categoryId);
        if (!empty($requestPath)) {
            /** @var $redirect Enterprise_UrlRewrite_Model_Redirect */
            $redirect = Mage::getModel('enterprise_urlrewrite/redirect')
                ->setIdentifier($requestPath)
                ->setTargetPath($this->_getProductTargetPath($product->getId(), $categoryId))
                ->setStoreId($storeId)
                ->setProductId($product->getId());
            if (null !== $categoryId) {
                $redirect->setCategoryId($categoryId);
            }

            if (!$redirect->exists()) {
                $redirect->save();
            }
        }
    }

    /**
     * @param int $productId
     * @param int|null $categoryId
     * @return string
     */
    protected function _getProductTargetPath($productId, $categoryId = null)
    {
        return empty($categoryId) ?
            sprintf(self::BASE_PRODUCT_TARGET_PATH, $productId) :
            sprintf(self::BASE_PRODUCT_CATEGORY_TARGET_PATH, $productId, $categoryId);
    }
}