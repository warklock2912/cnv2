<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Search_Priority {

    public function toArray() {
        return array(
            1 => Mage::helper('mageworx_searchsuite')->__('Highest'),
            2 => Mage::helper('mageworx_searchsuite')->__('High'),
            3 => Mage::helper('mageworx_searchsuite')->__('Medium'),
            4 => Mage::helper('mageworx_searchsuite')->__('Low'),
            5 => Mage::helper('mageworx_searchsuite')->__('Lowest')
        );
    }

    public function toOptionArray() {
        $array = array();
        foreach ($this->toArray() as $key => $item) {
            $array[] = array('value' => $key, 'label' => $item);
        }
        return $array;
    }

}
