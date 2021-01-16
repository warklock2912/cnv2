<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_System_Config_Source_Category_Fields {

    protected $_options;

    public function toOptionArray() {
        if (!$this->_options) {
            $this->_options = array(
                array('value' => 'name', 'label' => Mage::helper('mageworx_searchsuite')->__('Name')),
                array('value' => 'description', 'label' => Mage::helper('mageworx_searchsuite')->__('Description')),
                array('value' => 'thumbnail', 'label' => Mage::helper('mageworx_searchsuite')->__('Thumbnail Image'))
            );
        }
        return $this->_options;
    }

}
