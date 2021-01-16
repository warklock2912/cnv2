<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

/**
 * @method Amasty_Customform_Model_Field_Option_Store setStoreId($storeId)
 * @method int getStoreId()
 * @method Amasty_Customform_Model_Field_Option_Store setFieldOptionId($fieldOptionId)
 * @method int getFieldOptionId()
 * @method Amasty_Customform_Model_Field_Option_Store setLabel($label)
 * @method string getLabel()
 */
class Amasty_Customform_Model_Field_Option_Store extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('amcustomform/field_option_store');
    }
}