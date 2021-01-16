<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Resource_Fulltext_Blog extends MageWorx_SearchSuite_Model_Resource_Fulltext_Abstract {

    public function _construct() {
        $this->_init('mageworx_searchsuite/awblog_fulltext', 'post_id');
    }

    protected function _rebuildStoreIndex($storeId, $postIds = null) {

        return $this;
    }

    protected function _getSearchableCategories($storeId, $postIds = null, $lastId = 0, $limit = 100) {

        $collection = Mage::getModel('blog/post')->getCollection();
        return $collection;
    }

}
