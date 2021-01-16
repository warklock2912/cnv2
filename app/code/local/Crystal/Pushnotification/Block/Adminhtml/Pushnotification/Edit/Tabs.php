<?php

class Crystal_Pushnotification_Block_Adminhtml_Pushnotification_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('form_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('pushnotification')->__('Notification Infomation'));
	}

	public function _beforeToHtml()
	{
		$this->addTab('form_session',array(
			'label' => Mage::helper('pushnotification')->__('Notification Infomation'),
			'title' => Mage::helper('pushnotification')->__('Notification Infomation'),
			'content' => $this->getLayout()->createBlock('pushnotification/adminhtml_pushnotification_edit_tab_form')->toHtml(),
		));
		return parent::_beforeToHtml();
	}
}