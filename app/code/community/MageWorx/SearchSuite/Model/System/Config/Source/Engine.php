<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_System_Config_Source_Engine {

    protected function _getEngines() {
        $engine = array(array('value' => 'xsearch', 'label' => Mage::helper('mageworx_searchsuite')->__('Extended Search')));
        if (is_array(Mage::registry('searchsuite_engines'))) {
            $engine = array_merge($engine, Mage::registry('searchsuite_engines'));
        }
        return $engine;
    }

    public function toOptionArray() {
        return $this->_getEngines();
    }

}
