<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Block_Adminhtml_Categories extends Mage_Adminhtml_Block_Widget_Grid_Container {
  public function __construct() {
    $this->_controller = 'adminhtml_categories';
    $this->_blockGroup = 'bannerads';
    $this->_headerText = Mage::helper('bannerads')->__('Manage Banner Categories');
    $this->_addButtonLabel = Mage::helper('bannerads')->__('Add New Category');
    parent::__construct();
  }
}