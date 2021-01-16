<?php

class Crystal_Campaignmanage_Block_Adminhtml_Cropanddrop_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'campaignmanage';
        $this->_controller = 'adminhtml_cropanddrop';
        $this->_updateButton('save', 'label', Mage::helper('campaignmanage')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('campaignmanage')->__('Delete'));
        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save and Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);
        $this->_formScripts[] = "

			function saveAndContinueEdit(){
				editForm.submit($('edit_form').action+'back/edit/');
			}
		";
    }

    public function getHeaderText()
    {
        if (Mage::registry('cropanddrop_data') && Mage::registry('cropanddrop_data')->getId()) {
            return Mage::helper('campaignmanage')->__("Edit Crop and Drop");
        } else {
            return Mage::helper('campaignmanage')->__('Add New Crop and Drop');
        }
    }
}