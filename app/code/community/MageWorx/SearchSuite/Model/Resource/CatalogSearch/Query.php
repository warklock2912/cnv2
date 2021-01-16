<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Resource_CatalogSearch_Query extends Mage_CatalogSearch_Model_Mysql4_Query {

    public function loadByQuery(Mage_Core_Model_Abstract $object, $value) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable())
                ->where('query_text=?', $value) // without synonym_for
                ->where('store_id=?', $object->getStoreId())
                ->order('synonym_for ASC')
                ->limit(1);
        if ($data = $this->_getReadAdapter()->fetchRow($select)) {
            $object->setData($data);
            $this->_afterLoad($object);
        }
        return $this;
    }

    public function clearSynonymsFor($terms) {
        $adapter = $this->_getWriteAdapter();
        $adapter->update($this->getMainTable(), array('synonym_for' => ''), array('query_text in (?)' => $terms));
        return $this;
    }

    public function getSynonymsByQueryText($storeId, $value) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable())
                ->where('synonym_for=?', $value)
                ->where('store_id=?', $storeId)
                ->order('synonym_for ASC');

        return $this->_getReadAdapter()->fetchAll($select);
    }

    public function getSynonyms($storeId, $queries) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable())
                ->where('synonym_for IN(?)', $queries)
                ->where('store_id=?', $storeId)
                ->order('synonym_for ASC');
        return $this->_getReadAdapter()->fetchAll($select);
    }

    public function getSynonymsForQueries($queries) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable())
                ->where('synonym_for IN(?)', $queries)
                ->order('synonym_for ASC');
        return $this->_getReadAdapter()->fetchAll($select);
    }

    public function getRelatedQueries($storeId, $query) {
        $equery = $this->_getReadAdapter()->quote($query);
        $equery = preg_replace('/^(\')/u', '', $equery);
        $equery = preg_replace('/(\')$/u', '', $equery);
        $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable())
                ->where("query_text REGEXP '[[:<:]]{$equery}[[:>:]]'")
                ->where('query_text!=?', $query)
                ->where('store_id=?', $storeId)
                ->order('query_text ASC')
                ->limit(3);
        return $this->_getReadAdapter()->fetchAll($select);
    }

}
