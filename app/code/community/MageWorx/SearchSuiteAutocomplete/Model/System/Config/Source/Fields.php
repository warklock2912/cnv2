<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteAutocomplete
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuiteAutocomplete_Model_System_Config_Source_Fields {

    public function toOptionArray() {
        return array(
            array('value' => 'suggest', 'label' => Mage::helper('mageworx_searchsuiteautocomplete')->__('Suggested')),
            array('value' => 'product', 'label' => Mage::helper('mageworx_searchsuiteautocomplete')->__('Products')),
            array('value' => 'category', 'label' => Mage::helper('mageworx_searchsuiteautocomplete')->__('Categories')),
            array('value' => 'cmspage', 'label' => Mage::helper('mageworx_searchsuiteautocomplete')->__('CMS Pages')),
        );
    }

}
