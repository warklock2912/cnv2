<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Block_Adminhtml_Bannerads extends Mage_Adminhtml_Block_Widget_Grid_Container {
  public function __construct() {
    $this->_controller = 'adminhtml_bannerads';
    $this->_blockGroup = 'bannerads';
    $this->_headerText = Mage::helper('bannerads')->__('Block Manager');
    $this->_addButtonLabel = Mage::helper('bannerads')->__('Add Block');
    parent::__construct();
  }
}