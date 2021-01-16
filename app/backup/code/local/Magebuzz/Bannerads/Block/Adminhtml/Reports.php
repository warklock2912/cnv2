<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Block_Adminhtml_Reports extends Mage_Adminhtml_Block_Widget_Grid_Container {
  public function __construct() {
    $this->_controller = 'adminhtml_reports';
    $this->_blockGroup = 'bannerads';
    $this->_headerText = Mage::helper('bannerads')->__('Report Banner');
    parent::__construct();
    $this->_removeButton('add');
  }
}