<?php
/*
* Copyright (c) 2015 www.magebuzz.com 
*/
class Magebuzz_Customaddress_Block_Adminhtml_City_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
	public function __construct() {
		parent::__construct();						 
		$this->_objectId = 'id';
		$this->_blockGroup = 'customaddress';
		$this->_controller = 'adminhtml_city';
		
		$this->_updateButton('save', 'label', Mage::helper('customaddress')->__('Save Item'));
		$this->_updateButton('delete', 'label', Mage::helper('customaddress')->__('Delete Item'));

		$this->_addButton('saveandcontinue', array(
			'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
			'onclick'   => 'saveAndContinueEdit()',
			'class'     => 'save',
		), -100);

		$this->_formScripts[] = "
			function toggleEditor() {
				if (tinyMCE.getInstanceById('customaddress_content') == null) {
					tinyMCE.execCommand('mceAddControl', false, 'customaddress_content');
				} else {
					tinyMCE.execCommand('mceRemoveControl', false, 'customaddress_content');
				}
			}

			function saveAndContinueEdit(){
				editForm.submit($('edit_form').action+'back/edit/');
			}
		";
	}

	public function getHeaderText() {
		if( Mage::registry('city_data') && Mage::registry('city_data')->getId() ) {
			return Mage::helper('customaddress')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('city_data')->getDefaultName()));
		} else {
			return Mage::helper('customaddress')->__('Add Item');
		}
	}
}