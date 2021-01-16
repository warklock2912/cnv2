<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/

class Magebuzz_Dealerlocator_Block_Adminhtml_Dealerlocator_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

  public function __construct() {
    parent::__construct();
    $this->setId('dealerlocator_tabs');
    $this->setDestElementId('edit_form');
    $this->setTitle(Mage::helper('dealerlocator')->__('Dealer Information'));
  }

  protected function _beforeToHtml() {
    $this->addTab('form_section', array('label' => Mage::helper('dealerlocator')->__('Dealer Information'), 'title' => Mage::helper('dealerlocator')->__('Dealer Information'), 'content' => $this->getLayout()->createBlock('dealerlocator/adminhtml_dealerlocator_edit_tab_form')->toHtml(),));

    return parent::_beforeToHtml();
  }
}