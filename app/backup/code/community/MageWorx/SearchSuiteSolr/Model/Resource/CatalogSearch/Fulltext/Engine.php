<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteSolr
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuiteSolr_Model_Resource_CatalogSearch_Fulltext_Engine extends MageWorx_SearchSuite_Model_Mysql4_CatalogSearch_Fulltext_Engine_Abstract {

    protected $_update_index = null;

    public function prepareResultForEngine($fulltextModel, $queryText, $query) {

        $helper = Mage::helper('mageworx_searchsuitesolr');
        $instance = $helper->getInstance();
        $multiplier = $helper->getPriorityMultiplier();
        $instance->setFieldWeights(array('data_index1' => pow($multiplier, 4), 'data_index2' => pow($multiplier, 3), 'data_index3' => pow($multiplier, 2), 'data_index4' => $multiplier, 'data_index5' => 1));
        $instance->addFilter('store_id', $query->getStoreId());
        $instance->status();
        $words = $this->filterQueryWords($queryText, $query->getStoreId());
        $queryText = join(' ', $words);
        $result = $instance->query($queryText);
        if ($result) {
            $result = $result['response'];
            if ($result['numFound'] > 0) {
                $data = array();
                foreach ($result['docs'] as $row) {
                    $data[] = '(' . $query->getId() . ',' . $row['product_id'] . ',' . $row['score'] . ')';
                }
                $sql = 'INSERT INTO `' . $this->getTable('catalogsearch/result') . '` VALUES ' . implode(',', $data) . ' ON DUPLICATE KEY UPDATE `relevance` = VALUES(`relevance`)';
                $adapter = $this->_getWriteAdapter();
                $adapter->query($sql);
            }
        }
        return true;
    }

    public function rebuildIndexForEngine($fulltextModel, $storeId = null, $productIds = null) {

        if (!is_null($productIds)) {
            $select = $this->_getReadAdapter()->select()
                    ->from($fulltextModel->getMainTable())
                    ->where('product_id IN (?)', $productIds);
            $this->_update_index = $this->_getReadAdapter()->fetchAll($select);
        }
        $fulltextModel->addCommitCallback(array($this, 'reindexSolr'));
        return true;
    }

    public function reindexSolr() {

        $instance = $instance = Mage::helper('mageworx_searchsuitesolr')->getInstance();
        if (!is_null($this->_update_index)) {
            $adapter = $this->_getWriteAdapter();
            $adapter->query('DELETE FROM ' . $this->getTable('mageworx_searchsuite/update_index'));
            $data = array();
            foreach ($this->_update_index as $row) {
                $data[] = '(' . $row['fulltext_id'] . ',' . $row['product_id'] . ')';
            }
            $sql = 'INSERT INTO `' . $this->getTable('mageworx_searchsuite/update_index') . '` (fulltext_id,product_id) VALUES ' . implode(',', $data);
            $adapter->query($sql);
            $instance->reindex(true);
        } else {
            $instance->reindex(false);
        }
    }

}
