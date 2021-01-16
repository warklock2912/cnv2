<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Block_Adminhtml_Categories_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
  public function __construct() {
    parent::__construct();
    $this->setId('categories_tabs');
    $this->setDestElementId('edit_form');
    $this->setTitle(Mage::helper('bannerads')->__('Category Information'));
  }

  protected function _beforeToHtml() {
    $this->addTab('form_section', array('label' => Mage::helper('bannerads')->__('Category Information'), 'title' => Mage::helper('bannerads')->__('Category Information'), 'content' => $this->getLayout()->createBlock('bannerads/adminhtml_categories_edit_tab_form')->toHtml(),));

    return parent::_beforeToHtml();
  }
}