<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_System_Config_Source_Accuracy {

    public function toOptionArray() {
        return array(
            array('value' => 'any', 'label' => Mage::helper('mageworx_searchsuite')->__('Any')),
            array('value' => 'all', 'label' => Mage::helper('mageworx_searchsuite')->__('All')),
            array('value' => 'exact', 'label' => Mage::helper('mageworx_searchsuite')->__('Exact')),
        );
    }

}
