<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
abstract class MageWorx_SearchSuite_Model_Resource_Fulltext_Abstract extends Mage_Core_Model_Mysql4_Abstract {

    protected function _saveIndexes($storeId, $indexes) {
        $values = array();
        $bind = array();

        foreach ($indexes as $id => $index) {
            $values[] = sprintf('(%s,%s,%s)', $this->_getWriteAdapter()->quoteInto('?', $id), $this->_getWriteAdapter()->quoteInto('?', $storeId), '?');
            $bind[] = $index;
        }

        if ($values) {
            $sql = "REPLACE INTO `{$this->getMainTable()}` VALUES"
                    . join(',', $values);
            $this->_getWriteAdapter()->query($sql, $bind);
        }

        return $this;
    }

    public function cleanIndex($storeId = null, $pageId = null) {
        $where = array();

        if ($storeId != null) {
            $where[] = $this->_getWriteAdapter()->quoteInto('store_id= ?', $storeId);
        }
        if ($pageId != null) {
            $where[] = $this->_getWriteAdapter()->quoteInto($this->_idFieldName . ' IN(?)', $pageId);
        }

        $this->_getWriteAdapter()->delete($this->getMainTable(), $where);

        return $this;
    }

    public function rebuildIndex($storeId = null, $ids = null) {
        if (is_null($storeId)) {
            $storeIds = array_keys(Mage::app()->getStores());
            foreach ($storeIds as $storeId) {
                $this->_rebuildStoreIndex($storeId, $ids);
            }
        } else {
            $this->_rebuildStoreIndex($storeId, $ids);
        }
        return $this;
    }

    protected function _performSearch($primaryKey, $resultTableName, $queryText, $query) {
        $searchType = Mage::helper('mageworx_searchsuite')->getSearchType($query->getStoreId());

        $bind = array(
            ':query' => $queryText
        );
        $like = array();
        $fulltextCond = '';
        $likeCond = '';
        $separateCond = '';

        if ($searchType == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_LIKE ||
                $searchType == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_COMBINE) {
            $words = Mage::helper('core/string')->splitWords($queryText, true, $query->getMaxQueryWords());
            $i = 0;
            foreach ($words as $word) {
                $like[] = '`data_index` LIKE :likew' . $i;
                $bind[':likew' . $i] = Mage::helper('mageworx_searchsuite')->escapeLikeValue($word, array('position' => 'any'));
                $i++;
            }
            if ($like) {
                $likeCond = '(' . join(' AND ', $like) . ')';
            }
        }

        if ($searchType == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_FULLTEXT ||
                $searchType == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_COMBINE) {
            $fulltextCond = 'MATCH (`data_index`) AGAINST (:query IN BOOLEAN MODE)';
        }
        if ($searchType == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_COMBINE && $likeCond) {
            $separateCond = ' OR ';
        }
        if (!$query->getId()) {
            $query->save();
        }

        $sql = sprintf("REPLACE INTO `{$resultTableName}` " .
                "(SELECT '%d', `{$primaryKey}`, MATCH (`data_index`) AGAINST (:query IN BOOLEAN MODE) " .
                "FROM `{$this->getMainTable()}` WHERE (%s%s%s) AND `store_id`='%d')", $query->getId(), $fulltextCond, $separateCond, $likeCond, $query->getStoreId()
        );
        $this->_getWriteAdapter()->query($sql, $bind);
        return $this;
    }

    public function resetSearchResults($field, $resultTable) {
        $adapter = $this->_getWriteAdapter();
        $adapter->update($this->getTable('catalogsearch/search_query'), array($field => 0));
        $adapter->delete($resultTable);
        return $this;
    }

}
