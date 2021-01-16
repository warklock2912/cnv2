<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/

class Magebuzz_Dealerlocator_Block_Adminhtml_Dealerlocator extends Mage_Adminhtml_Block_Widget_Grid_Container {
  public function __construct() {
    $this->_controller = 'adminhtml_dealerlocator';
    $this->_blockGroup = 'dealerlocator';
    $this->_headerText = Mage::helper('dealerlocator')->__('Manage Dealers');
    $this->_addButtonLabel = Mage::helper('dealerlocator')->__('Add New Dealer');
    parent::__construct();
  }
}