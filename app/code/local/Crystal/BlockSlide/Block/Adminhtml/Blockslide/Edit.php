<?php

class Crystal_BlockSlide_Block_Adminhtml_Blockslide_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();
		$this->_objectId = 'id';
		$this->_blockGroup = 'blockslide';
		$this->_controller = 'adminhtml_blockslide';
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
		if (Mage::registry('blockslide_data') && Mage::registry('blockslide_data')->getId()) {
			return Mage::helper('blockslide')->__("Edit '%s'", $this->htmlEscape(Mage::registry('blockslide_data')->getImage()));
		} else {
			return Mage::helper('blockslide')->__('Add New Slide');
		}
	}
}