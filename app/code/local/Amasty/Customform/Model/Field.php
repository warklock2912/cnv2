<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

/**
 * @method Amasty_Customform_Model_Field setCode($code)
 * @method string getCode()
 * @method Amasty_Customform_Model_Field setInputType($inputType)
 * @method string getInputType()
 * @method Amasty_Customform_Model_Field setDefaultValue($defaultValue)
 * @method string getDefaultValue()
 * @method Amasty_Customform_Model_Field setFrontendClass($frontendClass)
 * @method string getFrontendClass()
 * @method Amasty_Customform_Model_Field setIsDeleted($isDeleted)
 * @method bool getIsDeleted()
 */

class Amasty_Customform_Model_Field extends Amasty_Customform_Model_Storable
{
    protected function _construct()
    {
        $this->_init('amcustomform/field');
    }

    protected function setupRelations()
    {
        $fieldOptionsRelation = new Amasty_Customform_Model_Mappable_Relation('option');
        $fieldOptionsRelation
            ->setChildEntityId('amcustomform/field_option')
            ->setJoinColumn('field_id')
            ;
        $this->registerChildRelation($fieldOptionsRelation);
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();

        if ($this->hasData('label')) {
            $this->setLabels($this->getData('label'));
        }

        if ($this->hasData('default_option')) {
            $key = $this->getData('default_option');
            $this->setDefaultOptionKey($key);
        }
    }

    protected function _afterSave()
    {
        parent::_afterSave();

        if (!$this->isDeleted()) {
            if ($this->getData('is_deleted') == 1 && $this->getOrigData('is_deleted') == 0) {
                foreach ($this->getFormFields() as $formField) {
                    /** @var Amasty_Customform_Model_Form_Field $formField */
                    $formField->setIsDeleted(1);
                    $formField->save();
                }
            }
        }
    }

    protected function setDefaultOptionKey($defaultKey)
    {
        foreach ($this->childKeyMap['option'] as $key => $option) {
            /** @var Amasty_Customform_Model_Field_Option $option */

            $option->setIsDefault($key == $defaultKey);
        }
    }

    public function getOptionById($id)
    {
        /** @var Amasty_Customform_Model_Field_Option $option */
        $option = $this->getChildById($this->getRelation('option'), $id);
        return $option;
    }

    public function setLabels(array $labels)
    {
        foreach ($labels as $storeId => $labelText) {
            /** @var Amasty_Customform_Model_Field_Store $storeData */
            $storeData = $this->getStoreData($storeId);
            if (is_null($storeData)) {
                $storeData = $this->createStoreData($storeId);
            }

            $storeData->setLabel($labelText);
        }
    }

    public function getLabel($storeId = 0)
    {
        /** @var Amasty_Customform_Model_Field_Store $storeData */
        $storeData = $this->getStoreData($storeId);
        return is_object($storeData) ? $storeData->getLabel() : null;
    }

    public function isOptionsAllowed()
    {
        $allowedInputTypes = array(
            Amasty_Customform_Helper_Data::INPUT_TYPE_SELECT,
            Amasty_Customform_Helper_Data::INPUT_TYPE_MULTISELECT,
        );

        return in_array($this->getInputType(), $allowedInputTypes);
    }

    public function isMultipleOptionsAllowed()
    {
        $allowedInputTypes = array(
            Amasty_Customform_Helper_Data::INPUT_TYPE_MULTISELECT,
        );

        return in_array($this->getInputType(), $allowedInputTypes);
    }

    public function getFieldOptions()
    {
        return $this->getChildrenCollection($this->getRelation('option'));
    }

    public function getEffectiveDefaultValue()
    {
        /** @var Amasty_Customform_Helper_Data $helper */
        $helper = Mage::helper('amcustomform');
        if (in_array($this->getInputType(), $helper->getInputTypesWithOptions())) {
            $defaultValue = array();
            foreach ($this->getFieldOptions() as $option) {
                /** @var Amasty_Customform_Model_Field_Option $option */

                if ($option->getIsDefault()) {
                    $defaultValue[] = $option->getId();
                }
            }
        } else {
            $defaultValue = $this->getDefaultValue();
        }

        return $defaultValue;
    }

    public function getFormFields()
    {
        /** @var Amasty_Customform_Model_Resource_Form_Field_Collection $collection */
        $collection = Mage::getModel('amcustomform/form_field')->getCollection();
        $collection->addFilter('field_id', $this->getId());
        $collection->addFilter('is_deleted', 0);

        return $collection;
    }

    public function getDefaultValueByInput($type)
    {
        $field = '';
        switch ($type) {
            case 'select':
            case 'gallery':
            case 'media_image':
                break;
            case 'multiselect':
                $field = null;
                break;

            case 'text':
            case 'statictext':
            case 'price':
            case 'image':
                $field = 'default_value_text';
                break;

            case 'textarea':
                $field = 'default_value_textarea';
                break;

            case 'date':
                $field = 'default_value_date';
                break;

            case 'boolean':
                $field = 'default_value_yesno';
                break;
        }

        return $field;
    }
}