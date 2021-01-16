<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
abstract class MageWorx_SearchSuite_Model_Indexer_Abstract extends Mage_Index_Model_Indexer_Abstract {

    public function reindex($storeId = null, $enityId = null) {
        $resourceModel = $this->_getIndexer()->getResource();
        $resourceModel->beginTransaction();
        try {
            $this->_getIndexer()->rebuildIndex($storeId, $enityId);
            $resourceModel->commit();
        } catch (Exception $e) {
            $resourceModel->rollBack();
            throw $e;
        }
    }

    public function remove($storeId = null, $enityId = null) {
        $resourceModel = $this->_getIndexer()->getResource();
        $resourceModel->beginTransaction();
        try {
            $this->_getIndexer()->removeIndex($storeId, $enityId);
            $resourceModel->commit();
        } catch (Exception $e) {
            $resourceModel->rollBack();
            throw $e;
        }
    }

    public function reindexAll() {
        $this->reindex();
    }

    public function isVisible() {
        return $this->_isVisible;
    }

}
