<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteAutocomplete
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuiteAutocomplete_Model_System_Config_Source_Animation {

    public function toOptionArray() {
        return array(
            array('value' => 'default', 'label' => Mage::helper('mageworx_searchsuiteautocomplete')->__('Spinner')),
            array('value' => 'nprogress', 'label' => Mage::helper('mageworx_searchsuiteautocomplete')->__('NProgress')),
        );
    }

}
