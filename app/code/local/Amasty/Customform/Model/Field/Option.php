<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

/**
 * @method Amasty_Customform_Model_Field_Option setFieldId($fieldId)
 * @method string getFieldId()
 * @method Amasty_Customform_Model_Field_Option setPosition($position)
 * @method int getPosition()
 * @method Amasty_Customform_Model_Field_Option setIsDefault($isDefault)
 * @method int getIsDefault()
 *
 * @method Amasty_Customform_Model_Field_Option_Store getStoreData($storeId)
 */
class Amasty_Customform_Model_Field_Option extends Amasty_Customform_Model_Storable
{
    protected function _construct()
    {
        $this->_init('amcustomform/field_option');
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();

        if ($this->getData('delete')) {
            $this->isDeleted(1);
        }
        if ($this->hasData('label')) {
            $this->setLabels($this->getData('label'));
        }
    }

    public function setLabels(array $labels)
    {
        foreach ($labels as $storeId => $labelText) {
            $storeData = $this->getStoreData($storeId);
            if (is_null($storeData)) {
                $storeData = $this->createStoreData($storeId);
            }

            $storeData->setLabel($labelText);
        }
    }

    public function getLabel($storeId = 0)
    {
        $storeData = $this->getStoreData($storeId);
        if(is_object($storeData) && $storeData->getLabel() !== null){
            $label = $storeData->getLabel();
        } else {
            $storeData = $this->getStoreData(0);
            $label = $storeData->getLabel();
        }
        return $label;
    }
}