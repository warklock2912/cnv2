<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Block_Adminhtml_Field_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        /** @var Amasty_Customform_Helper_Data $helper */
        $helper = Mage::helper('amcustomform');

        $field = $this->getField();
        $defaultValue = $field->getDefaultValue();
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => $helper->__('Field Information'),
            'class'     => 'fieldset-wide',
        ));

        if ($field->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'id',
            ));
        }

        $fieldset->addField('code', 'text', array(
            'name'      => 'code',
            'label'     => $helper->__('Code'),
            'title'     => $helper->__('Code'),
            'required'  => true,
            'class'     => 'validate-code',
            'value'     => $field->getCode()
        ));


        switch($field->getInputType()){
            case 'statictext':
            case 'text':
                $this->getField()->setData('default_value_text',$defaultValue);
                break;
            case 'textarea':
                $this->getField()->setData('default_value_textarea',$defaultValue);
                break;
            case 'date':
                $this->getField()->setData('default_value_date',$defaultValue);
                break;
            case 'boolean':
                $this->getField()->setData('default_value_yesno',$defaultValue);
                break;
        }
        $inputTypes = $helper->getInputTypes();
        $inputTypesOptions = array(
            'name' => 'input_type',
            'label' => $helper->__('Input Type'),
            'title' => $helper->__('Input Type'),
            'value' => 'text',
            'values' => $inputTypes,
            'required' => true,
        );
        if (!$field->getId()) {
            $inputTypesOptions['after_element_html'] = '<br><small>' . Mage::helper('amcustomform')->__('Not changeable after you save the field') . '</small>';
        }
        $fieldset->addField('input_type', 'select', $inputTypesOptions);

        $yesno = array(
            array(
                'value' => 1,
                'label' => $helper->__('Yes')
            ),
            array(
                'value' => 0,
                'label' => $helper->__('No')
            ),
        );
        $labelDefaultValue = $helper->__('Default Value');
        /*$fieldset->addField('default_value', 'text', array(
            'name'      => 'default_value',
            'label'     => $labelDefaultValue,
            'title'     => $labelDefaultValue,
            'required'  => false,
        ));*/

        $fieldset->addField(
            'default_value_text', 'text', array(
                'name'  => 'default_value_text',
                'label' => $labelDefaultValue,
                'title' => $labelDefaultValue
            )
        );

        $fieldset->addField(
            'default_value_yesno', 'select', array(
                'name'   => 'default_value_yesno',
                'label'  => $labelDefaultValue,
                'title'  => $labelDefaultValue,
                'values' => $yesno
            )
        );

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(
            Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
        );
        $fieldset->addField(
            'default_value_date', 'date', array(
                'name'   => 'default_value_date',
                'label'  => $labelDefaultValue,
                'title'  => $labelDefaultValue,
                'image'  => $this->getSkinUrl(
                    'images/grid-cal.gif',
                    array('_area' => 'adminhtml', '_package' => 'default')
                ),
                'format' => $dateFormatIso
            )
        );

        $fieldset->addField(
            'default_value_textarea', 'textarea', array(
                'name'  => 'default_value_textarea',
                'label' => $labelDefaultValue,
                'title' => $labelDefaultValue
            )
        );

        /*$validationRules = $helper->getValidationRules();
        $fieldset->addField('frontend_class', 'multiselect', array(
            'name'  => 'frontend_class',
            'label' => $helper->__('Input Validation'),
            'title' => $helper->__('Input Validation'),
            'values'=> $validationRules,
            'onchange'=> "
             jQuery('#default_value_text').attr('class','input-text');
             jQuery('#default_value_text').addClass(jQuery(this).val().join(' '))
            "
        ));*/

        $fieldset->addField(
            'required', 'select', array(
                'name'  => 'required',
                'label' => $helper->__('Required'),
                'title' => $helper->__('Required'),
                'values'=> array(
                    array(
                        'value'=>1,
                        'label'=>'Yes'
                    ),
                    array(
                        'value'=>0,
                        'label'=>'No'
                    )
                )
            )
        );
        $fieldset->addField(
            'max_length', 'text', array(
                'name'  => 'max_length',
                'label' => $helper->__('Max Length'),
                'title' => $helper->__('Max Length'),
            )
        );


        if ($field->getId()) {
            $form->getElement('code')->setDisabled(1);
            $form->getElement('input_type')->setDisabled(1);
        }

        $form->setValues($this->getField()->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return Amasty_Customform_Model_Field
     */
    protected function getField()
    {
        return Mage::registry('amcustomform_current_field');
    }

}