<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/

class Magebuzz_Dealerlocator_Block_Adminhtml_Import extends Mage_Adminhtml_Block_Widget_Form_Container {
  public function __construct() {
    parent::__construct();
    $this->_updateButton('save', 'label', Mage::helper('dealerlocator')->__('Import'));
    $this->_removeButton('delete');
    $this->_removeButton('back');

    $this->_blockGroup = 'dealerlocator';
    $this->_controller = 'adminhtml';
    $this->_mode = 'import';
  }

  public function getHeaderText() {
    return Mage::helper('dealerlocator')->__('Add dealers');
  }
}