<?php

/*
 * Copyright (c) 2013 www.magebuzz.com 
 */

class Magebuzz_Imagehome_Block_Adminhtml_Imagehome_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('imagehome_form', array('legend' => Mage::helper('imagehome')->__('Item information')));
        $fieldset->addField('title', 'text', array(
            'label' => Mage::helper('imagehome')->__('Title'),
            'class' => 'required-entry',
            'required' => true,
            'renderer' => 'Magebuzz_Imagehome_Block_Adminhtml_Imagehome_Grid_Renderer_Image'
        ));

        if (Mage::getSingleton('adminhtml/session')->getImagehomeData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getImagehomeData());
            Mage::getSingleton('adminhtml/session')->setImagehomeData(null);
        } elseif (Mage::registry('imagehome_data')) {
            $form->setValues(Mage::registry('imagehome_data')->getData());
        }
        
        $template = 'imagehome/imagehome.phtml';
        $this->setTemplate($template);
        return parent::_prepareForm();
    }

}
