<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Resource_Indexer_Cmspage extends Mage_Core_Model_Resource_Db_Abstract {

    protected function _construct() {
        $this->_init('mageworx_searchsuite/fulltext_cmspage', 'page_id');
    }

}
