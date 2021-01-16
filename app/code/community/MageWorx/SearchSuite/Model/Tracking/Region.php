<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Tracking_Region extends Mage_Core_Model_Abstract {

    protected function _construct() {
        parent::_construct();
        $this->_init('mageworx_searchsuite/tracking_region');
    }

}
