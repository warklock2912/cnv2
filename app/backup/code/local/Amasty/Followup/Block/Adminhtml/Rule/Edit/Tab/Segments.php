<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */ 
class Amasty_Followup_Block_Adminhtml_Rule_Edit_Tab_Segments extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Followup_Helper_Data */
        $hlp = Mage::helper('amfollowup');
    
        $fldInfo = $form->addFieldset('general', array('legend'=> $hlp->__('Segment')));
        
        
        
        $fldInfo->addField('segments', 'multiselect', array(
            'label'     => $hlp->__('Segments'),
            'name'      => 'segments[]',
            'values'    => $hlp->getSegmentsOptions(),
        ));
        
        //set form values
        $form->setValues($this->getModel()); 
        
        return parent::_prepareForm();
    }
}