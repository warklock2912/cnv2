<?php

class Crystal_BlockSlide_Block_Adminhtml_Blockslide extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct() {
		$this->_controller = 'adminhtml_blockslide';
		$this->_blockGroup = 'blockslide';
		$this->_headerText = Mage::helper('blockslide')->__('Manage Block Slide(s)');
		parent::__construct();
	}
}