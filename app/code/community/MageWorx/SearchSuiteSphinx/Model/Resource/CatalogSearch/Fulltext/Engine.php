<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteSphinx
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuiteSphinx_Model_Resource_CatalogSearch_Fulltext_Engine extends MageWorx_SearchSuite_Model_Resource_CatalogSearch_Fulltext_Engine_Abstract {

    const DEFAULT_INDEX_NAME = 'catalogsearch_index';

    public function prepareResultForEngine($fulltextModel, $queryText, $query) {

        $helper = Mage::helper('mageworx_searchsuitesphinx');
        $sphinxIndexName = ($helper->getSphinxIndexName() != '') ? $helper->getSphinxIndexName() : self::DEFAULT_INDEX_NAME ;
        $instance = $helper->getInstance();
        $multiplier = $helper->getPriorityMultiplier();
        $instance->setFieldWeights(array('data_index1' => ceil(pow($multiplier, 4)), 'data_index2' => ceil(pow($multiplier, 3)), 'data_index3' => ceil(pow($multiplier, 2)), 'data_index4' => ceil($multiplier), 'data_index5' => 1));
        $instance->SetRankingMode($helper->getRankingMode());
        $instance->SetSortMode(SPH_SORT_RELEVANCE);
        $instance->SetLimits(0, 1000, 1000);
        $instance->SetFilter('store_id', array($query->getStoreId(), 0));
        $instance->SetMatchMode($helper->getMatchMode());
        $words = $this->filterQueryWords($queryText, $query->getStoreId());
        $queryText = join(' ', $words);
        $result = $instance->Query('*'.$queryText.'*', $sphinxIndexName);
        if (!$result) {
            return false;
        } else {
            if ($result['total'] > 0) {
                $data = array();
                foreach ($result['matches'] as $row) {
                    $data[] = '(' . $query->getId() . ',' . $row['attrs']['product_id'] . ',' . $row['weight'] . ')';
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
            $adapter = $this->_getWriteAdapter();
            $select = $this->_getReadAdapter()->select()
                    ->from($fulltextModel->getMainTable(), array('fulltext_id', 'product_id'))
                    ->where('product_id IN (?)', $productIds);
            $sql = 'INSERT INTO `' . $this->getTable('mageworx_searchsuite/update_index') . '`(fulltext_id,product_id) (' . $select->__toString() . ')';
            $adapter->query($sql);
        }
        return true;
    }

    public function formatResult($matches = array()) {
        $rc = array();
    }

}
