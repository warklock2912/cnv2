<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Block_Related extends Mage_Core_Block_Template {

    protected function getRelatedSearches() {
        $query = Mage::helper('catalogsearch')->getQuery();
        $related = $query->getResource()->getRelatedQueries(Mage::app()->getStore()->getId(), $query->getQueryText());
        return $related;
    }

}
