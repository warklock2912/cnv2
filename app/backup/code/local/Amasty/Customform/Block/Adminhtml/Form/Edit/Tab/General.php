<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Block_Adminhtml_Form_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $helper = Mage::helper('amcustomform');
        $formModel = $this->getFormModel();

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('amcustomform')->__('Form Information'),
            'class'     => 'fieldset-wide',
        ));

        if ($formModel->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'id',
            ));
        }

        $codeFieldOptions = array(
            'name'      => 'code',
            'label'     => Mage::helper('amcustomform')->__('Code'),
            'title'     => Mage::helper('amcustomform')->__('Code'),
            'required'  => true,
            'class'     => 'validate-code',
        );
        if ($formModel->getId()) {
            $codeFieldOptions['disabled'] = 'desabled';
        }
        $fieldset->addField('code', 'text', $codeFieldOptions);

        $fieldset->addField('success_url', 'text', array(
            'name'      => 'success_url',
            'label'     => Mage::helper('amcustomform')->__('Success URL'),
            'title'     => Mage::helper('amcustomform')->__('Success URL'),
            'required'  => true,
        ));

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

        $fieldset->addField(
            'captcha', 'select', array(
                'name'   => 'captcha',
                'label'  => $helper->__('Use CAPTCHA'),
                'title'  => $helper->__('Use CAPTCHA'),
                'values' => $yesno
            )
        );
        $fieldset->addField(
            'notification', 'select', array(
                'name'   => 'notification',
                'label'  => $helper->__('Send notification to email'),
                'title'  => $helper->__('Send notification to email'),
                'values' => $yesno
            )
        );

        $form->setValues($this->getFormModel()->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return Amasty_Customform_Model_Form
     */
    protected function getFormModel()
    {
        return Mage::registry('amcustomform_current_form');
    }

}