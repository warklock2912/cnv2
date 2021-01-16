<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/

class Magebuzz_Dealerlocator_Block_Adminhtml_Dealerlocator_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
  public function __construct() {
    parent::__construct();

    $this->_objectId = 'id';
    $this->_blockGroup = 'dealerlocator';
    $this->_controller = 'adminhtml_dealerlocator';

    $this->_updateButton('save', 'label', Mage::helper('dealerlocator')->__('Save Dealer'));
    $this->_updateButton('delete', 'label', Mage::helper('dealerlocator')->__('Delete Dealer'));

    $this->_addButton('saveandcontinue', array('label' => Mage::helper('adminhtml')->__('Save And Continue Edit'), 'onclick' => 'saveAndContinueEdit()', 'class' => 'save',), -100);

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
    if (Mage::registry('dealerlocator_data') && Mage::registry('dealerlocator_data')->getId()) {
      return Mage::helper('dealerlocator')->__("Edit '%s'", $this->htmlEscape(Mage::registry('dealerlocator_data')->getTitle()));
    } else {
      return Mage::helper('dealerlocator')->__('Add New Dealer');
    }
  }
}