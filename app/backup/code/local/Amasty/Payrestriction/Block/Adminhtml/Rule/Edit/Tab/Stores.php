<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Payrestriction
 */ 
class Amasty_Payrestriction_Block_Adminhtml_Rule_Edit_Tab_Stores extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Payrestriction_Helper_Data */
        $hlp = Mage::helper('ampayrestriction');
    
        $fldStore = $form->addFieldset('apply_in', array('legend'=> $hlp->__('Apply In')));
        $fldStore->addField('for_admin', 'select', array(
          'label'     => $hlp->__('Admin Area'),
          'name'      => 'for_admin',
          'values'    => array(
            array(
                'value' => 0,
                'label' => Mage::helper('catalog')->__('No')
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('catalog')->__('Yes')
            )),
        ));         
        
        $fldStore->addField('stores', 'multiselect', array(
            'label'     => $hlp->__('Stores'),
            'name'      => 'stores[]',
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(),
            'note'      => $hlp->__('Leave empty or select all to apply the rule to any store'), 
        ));  

        $fldCust = $form->addFieldset('apply_for', array('legend'=> $hlp->__('Apply For')));
        $fldCust->addField('cust_groups', 'multiselect', array(
            'name'      => 'cust_groups[]',
            'label'     => $hlp->__('Customer Groups'),
            'values'    => $hlp->getAllGroups(),
            'note'      => $hlp->__('Leave empty or select all to apply the rule to any group'),
        ));

//        $fldCust->addField('cust_ids', 'text', array(
//          'label'     => $hlp->__('Individual Customers'),
//          'name'      => 'cust_ids',
//          'note'      => $hlp->__('Provide comma separated IDs like 71, 22'),
//        ));               
        
        //set form values
        $form->setValues(Mage::registry('ampayrestriction_rule')->getData()); 
        
        return parent::_prepareForm();
    }
}