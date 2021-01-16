<?php
/*
* Copyright (c) 2013 www.magebuzz.com 
*/
class Magebuzz_Countingdown_Block_Adminhtml_Countingdown_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
  public function __construct() {
		parent::__construct();
		$this->setId('countingdown_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('countingdown')->__('Item Information'));
  }

  protected function _beforeToHtml() {
		$this->addTab('form_section', array(
			'label'     => Mage::helper('countingdown')->__('Item Information'),
			'title'     => Mage::helper('countingdown')->__('Item Information'),
			'content'   => $this->getLayout()->createBlock('countingdown/adminhtml_countingdown_edit_tab_form')->toHtml(),
		));
	 
		return parent::_beforeToHtml();
  }
}