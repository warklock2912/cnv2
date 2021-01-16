<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoHtmlSitemap
 */


class Amasty_SeoHtmlSitemap_Model_Sitemap
{
	/** @var int */
	protected $_storeId;
	/** @var Amasty_SeoHtmlSitemap_Helper_Data  */
	protected $_helper;

	public function __construct()
	{
		$this->_storeId = Mage::app()->getStore()->getId();
		$this->_helper = Mage::helper('amseohtmlsitemap');
	}

	/**
	 * Products
	 *
	 * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
	 */
	public function getProducts($stock = false, $splitLetters = false)
	{
		/** @var Amasty_SeoToolKit_Model_Data $toolKitModel */
		$toolKitModel = Mage::getModel('amseotoolkit/data');
		$collection = $toolKitModel->getProductCollection();
		$collection->addAttributeToFilter(
			'am_hide_from_html_sitemap',
			array(
				 array('neq' => 1),
				 array('null' => true)
			),
			'left'
		);

		$collection->addAttributeToSort('name', 'ASC');

        if ($stock) {
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
        }

        if ($splitLetters) {
            $letterGroups = array();
            foreach ($collection as $item) {
                $letter = strtoupper(substr($item->getName(), 0, 1));
                if (is_numeric($letter) || $letter == ' ') {
                    $letter = '#';
                }

                $letterGroups[$letter]['letter'] = $letter;
                $letterGroups[$letter]['items'][] = $item;
            }

            return $letterGroups;
        }

        return $collection;
	}

	/**
	 * Categories
	 *
	 * @return array
	 */
	public function getCategories($isTree = false)
	{
		$parentId = Mage::app()->getStore()->getRootCategoryId();
        if (!$isTree) {
            $ids = Mage::getModel('catalog/category')->load($parentId)->getAllChildren();
            $catIds = explode(',', $ids);

            if (($key = array_search($parentId, $catIds)) !== false) {
                unset($catIds[$key]);
            }

            $list = Mage::getModel('catalog/category')->getCollection()->addIdFilter($catIds)->addAttributeToSelect('*')->addAttributeToFilter('am_hide_from_html_sitemap', array(array('neq' => 1), array('null' => true)), 'left');

            return $list;
        }
		/** @var Mage_Catalog_Model_Resource_Category_Tree $tree */
		$tree = Mage::getResourceSingleton('catalog/category_tree')->load();
		$root = $tree->getNodeById($parentId);

		/** @var Amasty_SeoToolKit_Model_Data $toolKitModel */
		$toolKitModel = Mage::getModel('amseotoolkit/data');
		$collection = $toolKitModel->getCategoryCollection();

		$collection->addAttributeToFilter(
			'am_hide_from_html_sitemap',
			array(
				 array('neq' => 1),
				 array('null' => true)
			),
			'left'
		);

		$tree->addCollectionData($collection, true);

		return $this->_nodeToArray($root);
	}

	public function getGallery()
	{
		if (!Mage::helper('core')->isModuleEnabled('Mageplace_Gallery')) {
			return false;
		}

		$collection = Mage::getResourceModel('mpgallery/album_collection')
			->addIsActiveFilter()
			->addStoreFilter()
			->addCustomerGroupFilter()
		;

		return $collection;
	}

	/**
	 * @param Varien_Data_Tree_Node $node
	 * @return array
	 */
	protected function _nodeToArray(Varien_Data_Tree_Node $node)
	{
		$result                = array();
		$result['category_id'] = $node->getId();
		$result['name']        = $node->getName();
		$result['level']       = $node->getLevel();
		$result['url']         = $node->getUrl();
		$result['children']    = array();

		foreach ($node->getChildren() as $child) {
			$result['children'][] = $this->_nodeToArray($child);
		}

		return $result;
	}

	/**
	 * Pages
	 *
	 * @return array
	 */
	public function getPages()
	{
		/** @var Mage_Cms_Model_Resource_Page_Collection $collection */
		$collection = Mage::getModel('cms/page')->getCollection();
		$helper = $this->_helper;

		//exclude pages
        $excludePages = array();
        if (Mage::getStoreConfig($helper::CONFIG_EXCLUDE_CMS_PAGES)) {
            $excludePagesConfig = Mage::getStoreConfig($helper::CONFIG_EXCLUDE_CMS_PAGES_PATH);
            $excludePagesIds = array();
            foreach (explode(',', $excludePagesConfig) as $item) {
                $excludePages[] = trim($item);
            }
        }

		$collection->setOrder('title', 'ASC');
		$collection->addFilter('is_active', array('eq' => 1));
		if (! empty($excludePagesIds)) {
			$collection->addFilter('page_id', array('nin' => $excludePagesIds), 'public');
		}

		if (! empty($excludePages)) {
			$collection->addFilter('identifier', array('nin' => $excludePages), 'public');
		}

		/*$collection->addFilter('identifier', array('neq' => Mage_Cms_Model_Page::NOROUTE_PAGE_ID), 'public');*/
		$collection->addStoreFilter(Mage::app()->getStore());

		$baseUrl = Mage::app()->getStore($this->_storeId)->getBaseUrl();
		$results = $collection->toOptionArray();

		$pagesList = array();
		foreach ($results as $item) {
            $pagesList[] = array(
                'value' => $baseUrl . $item['value'],
                'label' => $item['label']
            );
		}

		return $pagesList;
	}

    public function getLandingPages()
    {
        if (!Mage::helper('core')->isModuleEnabled('Amasty_Xlanding')) {
            return false;
        }

        $collection = Mage::getModel('amlanding/resource_cms_page')->getCollection($this->_storeId);

        $resultArray = array();
        foreach ($collection as $one) {
            $title = $one->getTitle();
            if (isset($title)) {
                $resultArray[] = array(
                    'text' => $one->getTitle(),
                    'url' => $one->getUrl(),
                );
            }
        }

        return $resultArray;
    }

	public function getLinks()
	{
		$helper = $this->_helper;
		$linksData = (string) Mage::getStoreConfig($helper::CONFIG_LINKS_PATH);
		$items = preg_split('/$\R?^/m', $linksData);

		$resultArray = array();
		foreach ($items as $item) {
            if (!strpos($item, ','))
                continue;

            list($linkText, $linkUrl) = explode(',', trim($item), 2);
			if (empty($linkText) || empty($linkUrl)) {
				continue;
			}

			$resultArray[] = array(
				'text' => htmlspecialchars(trim($linkText)),
				'url' => htmlspecialchars(trim($linkUrl))
			);
		}

		return $resultArray;
	}

}