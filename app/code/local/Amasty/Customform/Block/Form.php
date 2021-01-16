<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Block_Form extends Mage_Core_Block_Template
{
    /** @var Varien_Data_Form */
    protected $form;

    public function __construct()
    {
        parent::_construct();
        //$this->_title($this->getHeaderText());
        $this->setTemplate('amcustomform/form.phtml');
    }

    public function getCaptcha(){
        $captcha = $this->getLayout()->createBlock('amcustomform/captcha','custom-captcha',array('form_id'=>'cap-custom-form-'.$this->getFormId()));
        return $captcha;
    }

    protected function _prepareLayout()
    {
        $this->form = new Varien_Data_Form(array(
            'id' => 'customform',
        ));
    }



    public function createElement(Amasty_Customform_Model_Form_Field $formField){
        $elementFactory = new Amasty_Customform_Varien_Data_Form_Element_Factory();
        $element = $elementFactory->createElement($formField,$this->getFormId());
        $element->setForm($this->form);
        $element->setLabel($formField->getLabel());
        $name = $element->getCode(). '_' . $formField->getId() . '_' . $element->getData('id') . '_' . $element->getInputType();
        $element->setId($name);
        $element->setData('name', $name);
        return $element;
    }

    public function getHeaderText()
    {
        return $this->getFormModel()->getCurrentStorable('title');
    }

    public function getFormModel()
    {
        $form = $this->getFormModelFromRegistry();

        if (!$form) {
            $form = $this->getFormModelById();
        }

        return $form;
    }

    protected function getFormModelFromRegistry()
    {

        /** @var Amasty_Customform_Model_Form $form */
        $form = Mage::registry('amcustomform_preview_form');
        return $form;
    }

    protected function getFormModelById()
    {
        $id = $this->getData('form_id');

        /** @var Amasty_Customform_Model_Form $form */
        $form = Mage::getModel('amcustomform/form');
        $form->load($id);
        if (!$form->getId()) {
            throw new Exception('Cannot find form with id ' . $id);
        }

        return $form;
    }
}
