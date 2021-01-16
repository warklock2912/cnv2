<?php
/*
* Copyright (c) 2015 www.magebuzz.com 
*/
class Magebuzz_Customaddress_Block_Adminhtml_City extends Mage_Adminhtml_Block_Widget_Grid_Container {
  public function __construct() {
    $this->_controller = 'adminhtml_city';
    $this->_blockGroup = 'customaddress';
    $this->_headerText = Mage::helper('customaddress')->__('Manage City');
    $this->_addButtonLabel = Mage::helper('customaddress')->__('Add City');
    parent::__construct();
  }
}