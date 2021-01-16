<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_System_Config_Source_Cmspage_Fields {

    protected $_options;

    public function toOptionArray() {
        if (!$this->_options) {
            $this->_options = array(
                array('value' => 'title', 'label' => Mage::helper('mageworx_searchsuite')->__('Title')),
                array('value' => 'content', 'label' => Mage::helper('mageworx_searchsuite')->__('Content'))
            );
        }
        return $this->_options;
    }

}
