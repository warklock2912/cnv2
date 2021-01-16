<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Resource_Tracking_Region_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('mageworx_searchsuite/tracking_region');
    }

    public function setQueryFilter($query) {
        $queryId = 0;
        if (is_object($query)) {
            $queryId = $query->getId();
        } else {
            $queryId = $query;
        }

        $this->getSelect()->where('query_id = ?', $queryId);
        return $this;
    }

}
