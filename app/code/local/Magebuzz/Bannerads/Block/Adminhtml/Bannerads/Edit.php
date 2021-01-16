<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Block_Adminhtml_Bannerads_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
  public function __construct() {
    parent::__construct();

    $this->_objectId = 'id';
    $this->_blockGroup = 'bannerads';
    $this->_controller = 'adminhtml_bannerads';

    $this->_updateButton('save', 'label', Mage::helper('bannerads')->__('Save Block'));
    $this->_updateButton('delete', 'label', Mage::helper('bannerads')->__('Delete Block'));

    $this->_addButton('saveandcontinue', array('label' => Mage::helper('adminhtml')->__('Save And Continue Edit'), 'onclick' => 'saveAndContinueEdit()', 'class' => 'save',), -100);
    $this->_addButton('add_new_image', array('label' => Mage::helper('bannerads')->__('Upload new image for this block'), 'onclick' => "setLocation('".$this->getAddNewImageUrl()."')", 'class' => 'add',), -200);


    $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('bannerads_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'bannerads_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'bannerads_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
  }

  public function getHeaderText() {
    if (Mage::registry('bannerads_data') && Mage::registry('bannerads_data')->getId()) {
      return Mage::helper('bannerads')->__("Edit Block '%s'", $this->htmlEscape(Mage::registry('bannerads_data')->getBlockTitle()));
    } else {
      return Mage::helper('bannerads')->__('Add Block');
    }
  }

  protected function getAddNewImageUrl() {
    return $this->getUrl('*/bannerads_images/new', array('from_block_id' => $this->getRequest()->getParam('id')));
  }

}