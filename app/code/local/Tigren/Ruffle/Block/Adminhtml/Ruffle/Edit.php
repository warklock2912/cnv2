<?php
/*
* Copyright (c) 2017 www.tigren.com 
*/
class Tigren_Ruffle_Block_Adminhtml_Ruffle_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
	public function __construct() {
		parent::__construct();						 
		$this->_objectId = 'id';
		$this->_blockGroup = 'ruffle';
		$this->_controller = 'adminhtml_ruffle';
		
		$this->_updateButton('save', 'label', Mage::helper('ruffle')->__('Save Prize'));
		$this->_updateButton('delete', 'label', Mage::helper('ruffle')->__('Delete Prize'));

		$this->_addButton('saveandcontinue', array(
			'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
			'onclick'   => 'saveAndContinueEdit()',
			'class'     => 'save',
		), -100);

		$this->_formScripts[] = "
			function toggleEditor() {
				if (tinyMCE.getInstanceById('ruffle_content') == null) {
					tinyMCE.execCommand('mceAddControl', false, 'ruffle_content');
				} else {
					tinyMCE.execCommand('mceRemoveControl', false, 'ruffle_content');
				}
			}

			function saveAndContinueEdit(){
				editForm.submit($('edit_form').action+'back/edit/');
			}

			function manualSelectWinner(type) {
				if (type == 'general') {
					var generalIds = $$('input[name^=general_ids]')[0];
					if (generalIds.value == '') {
						alert('Please select at least one member');
						return;
					}
					$('edit_form').action = '" . $this->getUrl('*/*/manualSelect', array('_secure' => true, 'group' => 'general')) . "';
				} else {
					var vipIds = $$('input[name^=vip_ids]')[0];
					if (vipIds.value == '') {
						alert('Please select at least one member');
						return;
					}
					$('edit_form').action = '" . $this->getUrl('*/*/manualSelect', array('_secure' => true, 'group' => 'vip')) . "';
				}
				$('edit_form').submit();

			}

			function emailToSelectedWinner() {
					var winnerIds = $$('input[name^=winner_ids]')[0];
					if (winnerIds.value == '') {
						alert('Please select at least one member');
						return;
					}
					$('edit_form').action = '" . $this->getUrl('*/*/emailToSelectedWinner', array('_secure' => true)) . "';
				
				$('edit_form').submit();

			}
		";
	}

	public function getHeaderText() {
		if( Mage::registry('current_ruffle') && Mage::registry('current_ruffle')->getId() ) {
			return Mage::helper('ruffle')->__("Edit Prize '%s'", $this->htmlEscape(Mage::registry('current_ruffle')->getTitle()));
		} else {
			return Mage::helper('ruffle')->__('Add Prize');
		}
	}
}