<?php
/*
* Copyright (c) 2013 www.magebuzz.com 
*/
class Magebuzz_Countingdown_Block_Adminhtml_Countingdown_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
	public function __construct() {
		parent::__construct();						 
		$this->_objectId = 'id';
		$this->_blockGroup = 'countingdown';
		$this->_controller = 'adminhtml_countingdown';
		
		$this->_updateButton('save', 'label', Mage::helper('countingdown')->__('Save Item'));
		$this->_updateButton('delete', 'label', Mage::helper('countingdown')->__('Delete Item'));

		$this->_addButton('saveandcontinue', array(
			'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
			'onclick'   => 'saveAndContinueEdit()',
			'class'     => 'save',
		), -100);

		$this->_formScripts[] = "
			function toggleEditor() {
				if (tinyMCE.getInstanceById('countingdown_content') == null) {
					tinyMCE.execCommand('mceAddControl', false, 'countingdown_content');
				} else {
					tinyMCE.execCommand('mceRemoveControl', false, 'countingdown_content');
				}
			}

			function saveAndContinueEdit(){
				editForm.submit($('edit_form').action+'back/edit/');
			}
		";
	}

	public function getHeaderText() {
		if( Mage::registry('countingdown_data') && Mage::registry('countingdown_data')->getId() ) {
			return Mage::helper('countingdown')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('countingdown_data')->getTitle()));
		} else {
			return Mage::helper('countingdown')->__('Add Item');
		}
	}
}