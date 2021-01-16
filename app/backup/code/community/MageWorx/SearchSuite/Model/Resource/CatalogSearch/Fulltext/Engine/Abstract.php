<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Resource_CatalogSearch_Fulltext_Engine_Abstract extends Mage_CatalogSearch_Model_Mysql4_Fulltext_Engine {

    public function saveEntityIndexes($storeId, $entityIndexes, $entity = 'product') {
        $searchHelper = Mage::helper('mageworx_searchsuite');
        $data = array();
        $storeId = (int) $storeId;
        foreach ($entityIndexes as $entityId => $index) {
            $data[] = array(
                'product_id' => (int) $entityId,
                'store_id' => $storeId,
                'data_index' => $index[0],
                'data_index1' => $searchHelper->prepareValue($index[1]),
                'data_index2' => $searchHelper->prepareValue($index[2]),
                'data_index3' => $searchHelper->prepareValue($index[3]),
                'data_index4' => $searchHelper->prepareValue($index[4]),
                'data_index5' => $searchHelper->prepareValue($index[5])
            );
        }
        if ($data) {
            $this->insertOnDuplicate_compatible($this->getMainTable(), $data, array('data_index', 'data_index1', 'data_index2', 'data_index3', 'data_index4', 'data_index5'));
        }
        return $this;
    }

    // for magento < 1.7.0.0 from CatalogSearch Fulltext Index Engine resource model
    public function getAllowedVisibility_compatible() {
        return Mage::getSingleton('catalog/product_visibility')->getVisibleInSearchIds();
    }

    // for magento < 1.7.0.0 from CatalogSearch Mysql resource helper model
    public function insertOnDuplicate_compatible($table, array $data, array $fields = array()) {
        return $this->_getWriteAdapter()->insertOnDuplicate($table, $data, $fields);
    }

    // search
    public function prepareResultForEngine($fulltextModel, $queryText, $query) {
        return false;
    }

    public function rebuildIndexForEngine($fulltextModel, $storeId = null, $productIds = null) {
        return false;
    }

    public function filterQueryWords($words, $storeId) {

        if (is_string($words)) {
            $words = Mage::helper('core/string')->splitWords(Mage::helper('mageworx_searchsuite')->prepareValue($words), true, Mage::getStoreConfig(Mage_CatalogSearch_Model_Query::XML_PATH_MAX_QUERY_WORDS, $storeId));
        }
        if (count($words) > 0) {
            $stopwordCollection = Mage::getResourceModel('mageworx_searchsuite/stopword_collection');
            $stopwordCollection->addWordFilter($words, $storeId);
            if ($stopwordCollection->count()) {
                foreach ($stopwordCollection as $item) {
                    if (isset($words[$item->getWord()])) {
                        unset($words[$item->getWord()]);
                    }
                }
            }
        }
        return $words;
    }

}
