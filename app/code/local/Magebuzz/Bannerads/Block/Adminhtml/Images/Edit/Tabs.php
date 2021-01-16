<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Block_Adminhtml_Images_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
  public function __construct() {
    parent::__construct();
    $this->setId('images_tabs');
    $this->setDestElementId('edit_form');
    $this->setTitle(Mage::helper('bannerads')->__('Banner Image Information'));
  }

  protected function _beforeToHtml() {
		$this->addTab('form_section', array('label' => Mage::helper('bannerads')->__('Banner Image Information'), 'title' => Mage::helper('bannerads')->__('Banner Image Information'), 'content' => $this->getLayout()->createBlock('bannerads/adminhtml_images_edit_tab_form')->toHtml(),));
		
    $this->addTab('form_bannerads_block', array(
      'label' => Mage::helper('bannerads')->__('Banner Block'),
      'title' => Mage::helper('bannerads')->__('Banner Block'),
      'class' => 'ajax',
      'url'   => $this->getUrl('adminhtml/bannerads_images/block_tab', array(
        '_current' => TRUE,
        'id' => $this->getRequest()->getParam('id'),
        'from_block_id' => $this->getRequest()->getParam('from_block_id')
      )),
    ));   

    return parent::_beforeToHtml();
  }
}