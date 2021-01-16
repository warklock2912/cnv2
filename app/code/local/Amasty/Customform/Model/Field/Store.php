<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

/**
 * @method Amasty_Customform_Model_Field_Store setStoreId($storeId)
 * @method int getStoreId()
 * @method Amasty_Customform_Model_Field_Store setFieldId($fieldId)
 * @method int getFieldId()
 * @method Amasty_Customform_Model_Field_Store setLabel($label)
 * @method string getLabel()
 */
class Amasty_Customform_Model_Field_Store extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('amcustomform/field_store');
    }
}