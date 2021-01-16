<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Block_Adminhtml_Images_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
  public function __construct() {
    parent::__construct();
    $this->_objectId = 'id';
    $this->_blockGroup = 'bannerads';
    $this->_controller = 'adminhtml_images';

    $this->_updateButton('save', 'label', Mage::helper('bannerads')->__('Save Banner'));
    $this->_updateButton('delete', 'label', Mage::helper('bannerads')->__('Delete Banner'));

    $this->_addButton('saveandcontinue', array('label' => Mage::helper('adminhtml')->__('Save And Continue Edit'), 'onclick' => 'saveAndContinueEdit()', 'class' => 'save',), -100);

    $this->_formScripts[] = "
			function toggleEditor() {
				if (tinyMCE.getInstanceById('images_content') == null) {
					tinyMCE.execCommand('mceAddControl', false, 'images_content');
				} else {
					tinyMCE.execCommand('mceRemoveControl', false, 'images_content');
				}
			}

			function saveAndContinueEdit(){
				editForm.submit($('edit_form').action+'back/edit/');
			}
		";
  }

  public function getHeaderText() {
    if (Mage::registry('images_data') && Mage::registry('images_data')->getId()) {
      return Mage::helper('bannerads')->__("Edit Banner Images '%s'", $this->htmlEscape(Mage::registry('images_data')->getBannerTitle()));
    } else {
      return Mage::helper('bannerads')->__('Add Banner Image');
    }
  }

  /*
  * set Load TinyMce to use Wysiwyg editor
  */
  protected function _prepareLayout() {
    parent::_prepareLayout();
    if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
      $this->getLayout()->getBlock('head')->setCanLoadTinyMce(TRUE);
    }
  }

  public function getBackUrl() {
    if ($this->getRequest()->getParam('from_block_id')) {
      return $this->getUrl('*/bannerads/edit', array('id' => $this->getRequest()->getParam('from_block_id')));
    }
  }
}
