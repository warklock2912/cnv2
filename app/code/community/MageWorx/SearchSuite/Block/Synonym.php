<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Block_Synonym extends Mage_Core_Block_Template {

    protected function getSynonym() {
        $query = Mage::helper('catalogsearch')->getQuery();
        $collection = Mage::getResourceModel('mageworx_searchsuite/synonym_collection')->addSynonymFilter($query->getQueryText(), $query->getStoreId());
        return $collection->getFirstItem()->getQueryText();
    }

}
