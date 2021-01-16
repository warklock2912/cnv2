<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Resource_Fulltext_Category extends MageWorx_SearchSuite_Model_Resource_Fulltext_Abstract {

    public function _construct() {
        $this->_init('mageworx_searchsuite/category_fulltext', 'category_id');
    }

    protected function _rebuildStoreIndex($storeId, $categoryIds = null) {
        $lastId = 0;
        $searchString = array('@&lt;script.*?&gt;.*?&lt;/script&gt;@si', '@&lt;style.*?&gt;.*?&lt;/style&gt;@si');
        $replaceString = array('', '');
        while (true) {
            $categories = $this->_getSearchableCategories($storeId, $categoryIds, $lastId);
            if ($categories->count() == 0) {
                break;
            }
            $indexes = array();
            foreach ($categories as $category) {

                $index = array();
                $index[] = $category->getName();
                if ($category->getDescription()) {
                    $html = trim(preg_replace($searchString, $replaceString, $category->getDescription()));
                    $html = preg_replace("#\s+#si", " ", trim(strip_tags($html)));
                    $index[] = html_entity_decode($html, ENT_QUOTES, "UTF-8");
                }
                $indexes[$category->getId()] = join(' ', $index);
            }
            $lastId += $categories->count();
            $this->_saveIndexes($storeId, $indexes);
        }

        return $this;
    }

    protected function _getSearchableCategories($storeId, $categoryIds = null, $lastId = 0, $limit = 100) {

        $collection = Mage::getModel('catalog/category')->getCollection();

        $collection->setStoreId($storeId)
                ->addAttributeToSelect(array('name', 'description'))
                ->addFieldToFilter('path', array('neq' => '1'))
                ->addAttributeToFilter('parent_id', array('neq' => '0'))
                ->addIsActiveFilter();

        if (!is_null($categoryIds)) {
            $collection->addIdFilter($categoryIds);
        }
        $collection->getSelect()->limit($limit, $lastId);
        return $collection;
    }

    public function prepareResult($object, $queryText, $query) {
        if (!$query->getIsCategoryProcessed()) {
            $this->_performSearch('category_id', $this->getTable('mageworx_searchsuite/category_result'), $queryText, $query);
            $query->setIsCategoryProcessed(1);
            $query->save();
        }

        return $this;
    }

    public function rebuildIndex($storeId = null, $ids = null) {
        $this->resetSearchResults('is_category_processed', $this->getTable('mageworx_searchsuite/category_result'));
        parent::rebuildIndex($storeId, $ids);
    }

}
