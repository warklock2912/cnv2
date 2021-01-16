<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */
class Amasty_Groupcat_Block_Adminhtml_Rules_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId   = 'id';
        $this->_blockGroup = 'amgroupcat';
        $this->_controller = 'adminhtml_rules';

        $this->_addButton('save_and_continue',
            array(
                'label'   => Mage::helper('salesrule')->__('Save and Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class'   => 'save'
            ),
            10
        );
        
        $this->_formScripts[] = " function saveAndContinueEdit(){ editForm.submit($('edit_form').action + 'continue/edit') } ";
        if(Mage::registry('amgroupcat_rules') && Mage::registry('amgroupcat_rules')->getData('rule_name')) {
            $this->_headerText = Mage::helper('amgroupcat')->__('Edit rule %s', Mage::registry('amgroupcat_rules')->getData('rule_name'));
        }
        else{
            $this->_headerText = Mage::helper('amgroupcat')->__('New rule');
        }


    }
}
