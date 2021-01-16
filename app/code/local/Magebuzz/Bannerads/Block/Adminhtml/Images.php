<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Block_Adminhtml_Images extends Mage_Adminhtml_Block_Widget_Grid_Container {
  public function __construct() {
    $this->_controller = 'adminhtml_images';
    $this->_blockGroup = 'bannerads';
    $this->_headerText = Mage::helper('bannerads')->__('Manage Banner Images');
    $this->_addButtonLabel = Mage::helper('bannerads')->__('Add New Image');
    parent::__construct();
  }
}