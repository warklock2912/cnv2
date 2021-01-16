<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Block_Adminhtml_Categories_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
  public function __construct() {
    parent::__construct();
    $this->_objectId = 'id';
    $this->_blockGroup = 'bannerads';
    $this->_controller = 'adminhtml_categories';

    $this->_updateButton('save', 'label', Mage::helper('bannerads')->__('Save Category'));
    $this->_updateButton('delete', 'label', Mage::helper('bannerads')->__('Delete Category'));

    $this->_addButton('saveandcontinue', array('label' => Mage::helper('adminhtml')->__('Save And Continue Edit'), 'onclick' => 'saveAndContinueEdit()', 'class' => 'save',), -100);

    $this->_formScripts[] = "
			function toggleEditor() {
				if (tinyMCE.getInstanceById('categories_content') == null) {
					tinyMCE.execCommand('mceAddControl', false, 'categories_content');
				} else {
					tinyMCE.execCommand('mceRemoveControl', false, 'categories_content');
				}
			}

			function saveAndContinueEdit(){
				editForm.submit($('edit_form').action+'back/edit/');
			}
		";
  }

  public function getHeaderText() {
    if (Mage::registry('category_data') && Mage::registry('category_data')->getId()) {
      return Mage::helper('bannerads')->__("Edit Category '%s'", $this->htmlEscape(Mage::registry('category_data')->getTitle()));
    } else {
      return Mage::helper('bannerads')->__('Add Category');
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
}
