<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
abstract class MageWorx_SearchSuite_Model_Fulltext_Abstract extends Mage_Core_Model_Abstract {

    public function removeIndex($storeId = null, $itemIds = null) {
        $this->getResource()->cleanIndex($storeId, $itemIds);
        return $this;
    }

    public function prepareResult(Mage_CatalogSearch_Model_Query $query) {
        $this->_getResource()->prepareResult($this, $query->getQueryText(), $query);
        return $this;
    }

}
