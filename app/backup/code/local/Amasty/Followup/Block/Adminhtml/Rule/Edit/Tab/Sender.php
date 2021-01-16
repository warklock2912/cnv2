<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */ 
class Amasty_Followup_Block_Adminhtml_Rule_Edit_Tab_Sender extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Followup_Helper_Data */
        $hlp = Mage::helper('amfollowup');
    
        $fldInfo = $form->addFieldset('general', array('legend'=> $hlp->__('Sender Details')));
                
        $fldInfo->addField('sender_name', 'text', array(
            'label'     => $hlp->__('Name'),
            'name'      => 'sender_name',
        ));
        
        $fldInfo->addField('sender_email', 'text', array(
            'label'     => $hlp->__('Email'),
            'name'      => 'sender_email',
        ));
        
        $fldInfo->addField('sender_cc', 'text', array(
            'label'     => $hlp->__('Sends copy of emails to'),
            'name'      => 'sender_cc',
        ));
        
        //set form values
        $form->setValues($this->getModel()); 
        
        return parent::_prepareForm();
    }
}