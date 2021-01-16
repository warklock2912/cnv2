<?php
/*
* Copyright (c) 2013 www.magebuzz.com 
*/
class Magebuzz_Countingdown_Block_Adminhtml_Countingdown extends Mage_Adminhtml_Block_Widget_Grid_Container {
  public function __construct() {
    $this->_controller = 'adminhtml_countingdown';
    $this->_blockGroup = 'countingdown';
    $this->_headerText = Mage::helper('countingdown')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('countingdown')->__('Add Item');
    parent::__construct();
  }
}