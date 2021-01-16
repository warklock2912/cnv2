<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Varien_Data_Form_Element_Factory
{


    public function createElement(Amasty_Customform_Model_Form_Field $formField, $formId)
    {
        $field = $formField->getField();
        $inputType = $field->getInputType();

        switch ($inputType) {
            case Amasty_Customform_Helper_Data::INPUT_TYPE_TEXT:
                $element = new Varien_Data_Form_Element_Text();
                break;

            case Amasty_Customform_Helper_Data::INPUT_TYPE_TEXTAREA:
                $element = new Varien_Data_Form_Element_Textarea();
                break;

            case Amasty_Customform_Helper_Data::INPUT_TYPE_BOOLEAN:
                $element = new Varien_Data_Form_Element_Select(
                    array(
                    'value'=>$field->getDefaultValue()
                    )
                );
                $element->setValues(
                    array('No','Yes')
                );
                break;

            case Amasty_Customform_Helper_Data::INPUT_TYPE_DATE;
                $element = new Varien_Data_Form_Element_Date(array(
                    'value'=>$field->getDefaultValue(),
                    'format'=>Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
                    'image' => Mage::getDesign()->getSkinUrl('images/grid-cal.gif',array('_package'=>'default')),
                    'readonly'=>'readonly'
                ));
                break;

            case Amasty_Customform_Helper_Data::INPUT_TYPE_MULTISELECT;
                $element = new Varien_Data_Form_Element_Multiselect(
                    array(
                        'value'=>$field->getDefaultValue()
                    )
                );
                $this->addOptionsToElement($element, $field);
                break;

            case Amasty_Customform_Helper_Data::INPUT_TYPE_SELECT;
                $element = new Varien_Data_Form_Element_Select(
                    array(
                        'value'=>$field->getDefaultValue()
                    )
                );
                $this->addOptionsToElement($element, $field);
                break;

            case Amasty_Customform_Helper_Data::INPUT_TYPE_STATIC_TEXT:
                $element = new Varien_Data_Form_Element_Note();
                $element->setText($formField->getEffectiveDefaultValue());
                break;

            case Amasty_Customform_Helper_Data::INPUT_TYPE_FILE:
                $element = new Varien_Data_Form_Element_File();
                break;
            case Amasty_Customform_Helper_Data::INPUT_TYPE_RADIO:
                $element = new Varien_Data_Form_Element_Radios();
                $this->addOptionsToElement($element, $field);
                break;
            case Amasty_Customform_Helper_Data::INPUT_TYPE_CHECKBOXES:
                $element = new Varien_Data_Form_Element_Checkboxes();
                $this->addOptionsToElement($element, $field);
                break;

            default:
                throw new Exception('Unknown input type ' . $inputType);
        }
        $element->addData($field->getData());
        $session = Mage::getSingleton('customer/session');

        $postData = $session->getData('customer-form-data-'.$formId);

        $key = $element->getCode().'_'.$formField->getId().'_'.$formField->getFieldId().'_'.$element->getInputType();

        if(isset($postData[$key])){
            $element->setValue($postData[$key]);
        }else{
            if($formField->getEffectiveDefaultValue() !== null){
                $element->setValue($formField->getEffectiveDefaultValue());
            }
        }

        return $element;
    }

    protected function addOptionsToElement(Varien_Data_Form_Element_Abstract $element, Amasty_Customform_Model_Field $field)
    {
        $options = $field->getFieldOptions();
        $values = array();
        $storeId = Mage::app()->getStore()->getStoreId();
        foreach($options as $option) {
            /** @var Amasty_Customform_Model_Field_Option $option */

            $values[$option->getId()]['label'] = $option->getLabel($storeId);
            $values[$option->getId()]['value'] = $option->getId();
        }
        $element->addElementValues($values);
    }
}