<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

/**
 * @method Amasty_Customform_Model_Form_Field setLineId($lineId)
 * @method int getLineId()
 * @method Amasty_Customform_Model_Form_Field setFieldId($fieldId)
 * @method int getFieldId()
 * @method Amasty_Customform_Model_Form_Field setSortOrder($sortOrder)
 * @method int getSortOrder()
 * @method Amasty_Customform_Model_Form_Field setRewriteDefaultValue($rewriteDefaultValue)
 * @method int getRewriteDefaultValue()
 * @method Amasty_Customform_Model_Form_Field setDefaultValue($defaultValue)
 * @method string getDefaultValue()
 * @method Amasty_Customform_Model_Form_Field setIsDeleted($isDeleted)
 * @method bool getIsDeleted()
 */
class Amasty_Customform_Model_Form_Field extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('amcustomform/form_field');
    }

    public function getValueType()
    {
        $attributeTypeMap = array(
            Amasty_Customform_Helper_Data::INPUT_TYPE_TEXT => Varien_Db_Ddl_Table::TYPE_VARCHAR,
            Amasty_Customform_Helper_Data::INPUT_TYPE_BOOLEAN => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
            Amasty_Customform_Helper_Data::INPUT_TYPE_DATE => Varien_Db_Ddl_Table::TYPE_DATE,
            Amasty_Customform_Helper_Data::INPUT_TYPE_SELECT => Varien_Db_Ddl_Table::TYPE_VARCHAR,
            Amasty_Customform_Helper_Data::INPUT_TYPE_TEXTAREA => Varien_Db_Ddl_Table::TYPE_TEXT,
            Amasty_Customform_Helper_Data::INPUT_TYPE_MULTISELECT => Varien_Db_Ddl_Table::TYPE_VARCHAR,
            Amasty_Customform_Helper_Data::INPUT_TYPE_STATIC_TEXT => Varien_Db_Ddl_Table::TYPE_VARCHAR,
            Amasty_Customform_Helper_Data::INPUT_TYPE_FILE => Varien_Db_Ddl_Table::TYPE_TEXT,
        );

        $inputType = $this->getField()->getInputType();
        if (isset($attributeTypeMap[$inputType])) {
            return $attributeTypeMap[$inputType];
        } else {
            throw new Exception('Unknown input type ' . $inputType);
        }
    }

    public function getEffectiveDefaultValue()
    {
        if ($this->getRewriteDefaultValue()) {
            $default = $this->getDefaultValue();
        } else {
            $default = $this->getField()->getEffectiveDefaultValue();
        }

        return $default;
    }

    public function getField()
    {
        /** @var Amasty_Customform_Model_Field $field */
        $field = Mage::getModel('amcustomform/field');
        $field->load($this->getFieldId());

        return $field;
    }

    public function getForm()
    {
        return $this->getLine()->getForm();
    }

    public function getLine()
    {
        /** @var Amasty_Customform_Model_Form_Line $line */
        $line = Mage::getModel('amcustomform/form_line');
        $line->load($this->getLineId());

        return $line;
    }

    protected function getSetup()
    {
        /** @var Amasty_Customform_Model_Mysql4_Setup $setup */
        $setup = Mage::getModel('amcustomform/mysql4_setup', 'amcustomform_setup');
        return $setup;
    }
}