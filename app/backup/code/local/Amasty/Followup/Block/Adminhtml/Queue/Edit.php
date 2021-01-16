<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

class Amasty_Followup_Block_Adminhtml_Queue_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId    = 'id';
        $this->_blockGroup = 'amfollowup'; 
        $this->_controller  = 'adminhtml_queue';
        
        parent::__construct();

        $this->_removeButton('reset');
        $this->_removeButton('delete');
        
        
        $this->_addButton('save_and_continue', array(
                'label'     => Mage::helper('salesrule')->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit()',
                'class' => 'save'
            ), 10);
        $this->_formScripts[] = " function saveAndContinueEdit(){ editForm.submit($('edit_form').action + 'continue/edit') } ";   
        
        
    }
    
    public function getHeaderText()
    {
        $header = Mage::helper('amfollowup')->__('Edit Queue Item');
        
        return $header;
    }
    
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current' => true));
    }
}
?>