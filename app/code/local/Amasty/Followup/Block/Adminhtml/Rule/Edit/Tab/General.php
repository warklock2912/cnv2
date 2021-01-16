<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */ 
class Amasty_Followup_Block_Adminhtml_Rule_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Followup_Helper_Data */
        $hlp = Mage::helper('amfollowup');
    
        $fldInfo = $form->addFieldset('general', array('legend'=> $hlp->__('General')));


        $fldInfo->addField('name', 'text', array(
            'label'     => $hlp->__('Name'),
            'required'  => true,
            'name'      => 'name'
        ));
                
        $fldInfo->addField('start_event_type', 'select', array(
            'label'     => $hlp->__('Start Event'),
            'name'      => 'start_event_type',
            'readonly' => !$this->getModel()->getId() ? false : true,
            'disabled' => !$this->getModel()->getId() ? false : true,
            'values'    => $hlp->getEventTypes(),
        ));

        if ($this->getModel()->getStartEventType() == Amasty_Followup_Model_Rule::TYPE_CUSTOMER_DATE)
        {
            $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
            $fldInfo->addField('customer_date_event', 'date', array(
                'label'     => $hlp->__('Date'),
                'required'  => true,
                'name'      => 'customer_date_event',
                'image'  => $this->getSkinUrl('images/grid-cal.gif'),
                'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
                'format'       => $dateFormatIso
            ));
        }
        
        $cancelTypes = array();
        foreach($hlp->getCancelTypes($this->getModel()->isOrderRelated()) as $key => $val){
            $cancelTypes[] = array(
                "value" => $key,
                "label" => $val
            );
        }
        if ($this->getModel()->getId()) {
            $fldInfo->addField('cancel_event_type', 'multiselect', array(
                'label'     => $hlp->__('Cancel Event'),
                'name'      => 'cancel_event_type[]',
                'values'    => $cancelTypes,
            ));



            $fldInfo->addField('to_subscribers', 'select', array(
                'label'     => $hlp->__('Send to Newsletter Subscribers Only'),
                'name'      => 'to_subscribers',
                'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(), 
            ));

            $fldInfo->addField('is_active', 'select', array(
                'label'     => $hlp->__('Is Active'),
                'name'      => 'is_active',
                'options'    => $hlp->getRuleStatuses(),
            ));
        } else {
            $fldInfo->addField('continue_button', 'note', array(
                'text' => $this->getChildHtml('continue_button'),
            ));
        }

        //set form values
        $form->setValues($this->getModel()); 
        
        return parent::_prepareForm();
    }
    
    protected function _prepareLayout()
    {
        $this->setChild('continue_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Continue'),
                    'onclick'   => "saveAndContinueEdit()",
                    'class'     => 'save'
                    ))
                );
        return parent::_prepareLayout();
    }
}