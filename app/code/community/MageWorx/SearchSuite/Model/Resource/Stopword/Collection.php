<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Resource_Stopword_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    protected function _construct() {
        $this->_init('mageworx_searchsuite/stopword');
    }

    public function addWordFilter($words, $storeId) {
        $this->getSelect()->where('word IN (?)', $words)->where('store_id = ?', $storeId);
        return $this;
    }

    public function delete() {
        foreach ($this->getItems() as $k => $item) {
            $item->delete();
            unset($this->_items[$k]);
        }
        return $this;
    }

}
