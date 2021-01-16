<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Resource_CatalogSearch_Fulltext_Collection extends Mage_CatalogSearch_Model_Mysql4_Fulltext_Collection {

    public function addSearchFilter($query) {
//        Mage::getSingleton('catalogsearch/fulltext')->prepareResult();
//
//        $this->getSelect()->joinInner(
//            array('search_result' => $this->getTable('catalogsearch/result')),
//            $this->getConnection()->quoteInto(
//                'search_result.product_id=e.entity_id AND search_result.query_id=?',
//                $this->_getQuery()->getId()
//            ),
//            array('relevance' => 'relevance')
//        );
//
//        return $this;
    }

}
