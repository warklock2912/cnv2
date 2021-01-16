<?php
/*
* Copyright (c) 2017 www.tigren.com 
*/
class Tigren_Ruffle_Block_Adminhtml_Ruffle extends Mage_Adminhtml_Block_Widget_Grid_Container {
  public function __construct() {
    $this->_controller = 'adminhtml_ruffle';
    $this->_blockGroup = 'ruffle';
    $this->_headerText = Mage::helper('ruffle')->__('Raffle Manager');
    $this->_addButtonLabel = Mage::helper('ruffle')->__('Add New Prize');
    parent::__construct();
  }
}