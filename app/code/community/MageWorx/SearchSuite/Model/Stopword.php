<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Stopword extends Mage_Core_Model_Abstract {

    protected function _construct() {
        $this->_init('mageworx_searchsuite/stopword');
    }

    public function loadByWord($word) {
        $this->_getResource()->loadByWord($this, $word);
        $this->_afterLoad();
        $this->setOrigData();
        return $this;
    }

    public function import($storeId, array $words) {
        $this->_getResource()->import($this, $storeId, $words);
        return $this;
    }

}
