<?php

class Crystal_Campaignmanage_Block_Adminhtml_Campaignmanage_View extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct() {
		parent::__construct();
		$this->_objectId = 'id';
		$this->_blockGroup = 'campaignmanage';
		$this->_controller = 'adminhtml_campaignmanage';
		$this->removeButton('save');
		$this->removeButton('reset');
		$this->removeButton('delete');


		$this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('dealerlocator_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'dealerlocator_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'dealerlocator_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
	}

	public function getHeaderText() {

		if (Mage::registry('campaignmanage_data') && Mage::registry('campaignmanage_data')->getId()) {
			return Mage::helper('campaignmanage')->__("Edit '%s'", $this->htmlEscape(Mage::registry('campaignmanage_data')->getTitle()));
		} else {
			return Mage::helper('campaignmanage')->__('Dealer Infomation');
		}
	}
}