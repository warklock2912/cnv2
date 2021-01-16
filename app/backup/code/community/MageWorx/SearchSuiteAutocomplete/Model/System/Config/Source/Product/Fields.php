<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteAutocomplete
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuiteAutocomplete_Model_System_Config_Source_Product_Fields {

    public function toOptionArray() {
        return array(
            array('value' => 'product_name', 'label' => Mage::helper('mageworx_searchsuiteautocomplete')->__('Product Name')),
            array('value' => 'sku', 'label' => Mage::helper('mageworx_searchsuiteautocomplete')->__('SKU')),
            array('value' => 'product_image', 'label' => Mage::helper('mageworx_searchsuiteautocomplete')->__('Product Image')),
            array('value' => 'reviews_rating', 'label' => Mage::helper('mageworx_searchsuiteautocomplete')->__('Reviews Rating')),
            array('value' => 'short_description', 'label' => Mage::helper('mageworx_searchsuiteautocomplete')->__('Short Description')),
            array('value' => 'description', 'label' => Mage::helper('mageworx_searchsuiteautocomplete')->__('Description')),
            array('value' => 'price', 'label' => Mage::helper('mageworx_searchsuiteautocomplete')->__('Price')),
            array('value' => 'add_to_cart_button', 'label' => Mage::helper('mageworx_searchsuiteautocomplete')->__('Add to Cart Button')),
        );
    }

}
