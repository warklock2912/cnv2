<?php

class Crystal_BlockSlide_Block_Adminhtml_Blockslide_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('form_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('blockslide')->__('Slide Infomation'));
	}

	public function _beforeToHtml()
	{
		$this->addTab('form_session',array(
			'label' => Mage::helper('blockslide')->__('Slide Infomation'),
			'title' => Mage::helper('blockslide')->__('Slide Infomation'),
			'content' => $this->getLayout()->createBlock('blockslide/adminhtml_blockslide_edit_tab_form')->toHtml(),
		));
		return parent::_beforeToHtml();
	}
}