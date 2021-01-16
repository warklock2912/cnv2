<?php

class Crystal_Pushnotification_Block_Adminhtml_Pushnotification_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();
		$this->_objectId = 'id';
		$this->_blockGroup = 'pushnotification';
		$this->_controller = 'adminhtml_pushnotification';
		$this->_updateButton('save', 'label', Mage::helper('pushnotification')->__('Send Message'));
		$this->_updateButton('delete', 'label', Mage::helper('pushnotification')->__('Delete'));

//
//        $this->_formScripts[] = "
//
//			function saveAndContinueEdit(){
//				editForm.submit($('edit_form').action+'back/edit/');
//			}
//
//		";
	}

	public function getHeaderText()
	{
		if (Mage::registry('notification_data') && Mage::registry('notification_data')->getId()) {
			return Mage::helper('pushnotification')->__('Notification Detail');
		} else {
			return Mage::helper('pushnotification')->__('Send Notification');
		}
	}
}