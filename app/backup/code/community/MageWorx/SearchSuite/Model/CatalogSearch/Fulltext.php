<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_CatalogSearch_Fulltext extends Mage_CatalogSearch_Model_Fulltext {

    public function prepareResult($query = null) {
        if (!$query instanceof Mage_CatalogSearch_Model_Query) {
            $query = Mage::helper('catalogsearch')->getQuery();
        }
        $queryText = Mage::helper('catalogsearch')->getQueryText();
        /* if ($query->getSynonymFor()) {
          $queryText = $query->getSynonymFor();
          } */
        $this->getResource()->prepareResult($this, $queryText, $query);
        return $this;
    }

}
