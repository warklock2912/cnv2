<?php
/*
* Copyright (c) 2015 www.magebuzz.com 
*/
class Magebuzz_Customaddress_Block_Adminhtml_Region extends Mage_Adminhtml_Block_Widget_Grid_Container {
  public function __construct() {
    $this->_controller = 'adminhtml_region';
    $this->_blockGroup = 'customaddress';
    $this->_headerText = Mage::helper('customaddress')->__('Manage Thailand Region/States');
    $this->_addButtonLabel = Mage::helper('customaddress')->__('Add Item');
    parent::__construct();
  }
}