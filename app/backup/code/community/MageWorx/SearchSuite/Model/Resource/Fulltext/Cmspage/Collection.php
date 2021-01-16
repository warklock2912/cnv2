<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Resource_Fulltext_Cmspage_Collection extends Mage_Cms_Model_Mysql4_Page_Collection {

    public function addSearchFilter(Mage_CatalogSearch_Model_Query $query) {
        Mage::getSingleton('mageworx_searchsuite/fulltext_cmspage')->prepareResult($query);

        $this->getSelect()->joinInner(
                array('search_result' => $this->getTable('mageworx_searchsuite/cmspage_result')), $this->getConnection()->quoteInto(
                        'search_result.page_id=main_table.page_id AND search_result.query_id = ?', $query->getId()
                ), array('relevance' => 'relevance')
        );
        return $this;
    }

}
