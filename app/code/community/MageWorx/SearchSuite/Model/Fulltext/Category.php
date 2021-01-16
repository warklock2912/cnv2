<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Fulltext_Category extends MageWorx_SearchSuite_Model_Fulltext_Abstract {

    protected function _construct() {
        $this->_init('mageworx_searchsuite/fulltext_category');
    }

    public function rebuildIndex($storeId = null, $pageIds = null) {

        Mage::dispatchEvent('mageworx_searchsuite_category_index_process_start', array(
            'store_id' => $storeId,
            'category_ids' => $pageIds
        ));
        $this->getResource()->rebuildIndex($storeId, $pageIds);
        Mage::dispatchEvent('mageworx_searchsuite_category_index_process_complete', array());
        return $this;
    }

}
