<?php
/*
* Copyright (c) 2018 Margin Frame Arrang
*/
class Marginframe_Shippop_Block_Adminhtml_Shippop extends Mage_Adminhtml_Block_Widget_Grid_Container {
  public function __construct() {
    $this->_controller = 'adminhtml_shippop';
    $this->_blockGroup = 'shippop';
    $this->_headerText = Mage::helper('shippop')->__('All list');
    parent::__construct();

  }
}