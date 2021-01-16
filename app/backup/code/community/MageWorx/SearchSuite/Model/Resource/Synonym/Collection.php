<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Resource_Synonym_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    protected function _construct() {
        $this->_init('mageworx_searchsuite/synonym');
    }

    public function addSynonymFilter($synonym, $storeId) {
        $this->getSelect()->join(array('query' => $this->getTable('catalogsearch/search_query')), 'main_table.query_id=query.query_id', array('query_text'))
                ->where('query.store_id IN (?)', array($storeId, 0))
                ->where('main_table.synonym = ?', $synonym);
        return $this;
    }

    public function delete() {
        foreach ($this->getItems() as $k => $item) {
            $item->delete();
            unset($this->_items[$k]);
        }
        return $this;
    }

    public function prepareSynonymsToCollection($collection) {
        $collection->getSelect()
                ->join(array('synonyms' => $this->getMainTable()), 'main_table.query_id=synonyms.query_id', array('synonyms' => new Zend_Db_Expr('GROUP_CONCAT(synonyms.synonym)')))
                ->group('main_table.query_id');
        return $this;
    }
    
	public function getCollection() {
		$collection = Mage::getResourceModel('catalogsearch/query_collection');
        $this->prepareSynonymsToCollection($collection);
        $select = $collection->getSelect()->assemble();
        $collection->getSelect()->reset()->from(array('main_table' => new Zend_Db_Expr('(' . $select . ')')), '*');
        return $collection;
	}
}
