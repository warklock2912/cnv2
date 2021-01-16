<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Resource_Fulltext_Category_Collection extends Mage_Catalog_Model_Resource_Category_Collection {

    public function addSearchFilter(Mage_CatalogSearch_Model_Query $query) {
        Mage::getSingleton('mageworx_searchsuite/fulltext_category')->prepareResult($query);

        $this->getSelect()->joinInner(
                array('search_result' => $this->getTable('mageworx_searchsuite/category_result')), $this->getConnection()->quoteInto(
                        'search_result.category_id=entity_id AND search_result.query_id = ?', $query->getId()
                ), array('relevance' => 'relevance')
        );
        return $this;
    }

}
