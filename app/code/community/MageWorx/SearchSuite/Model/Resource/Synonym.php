<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Resource_Synonym extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('mageworx_searchsuite/synonyms', 'id');
    }

}
