<?php

	/*
	* Copyright (c) 2015 www.magebuzz.com
	*/

	class Magebuzz_Bannerads_Block_Adminhtml_Bannerads_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
	{

		public function __construct()
		{
			parent::__construct();
			$this->setId('bannerads_tabs');
			$this->setDestElementId('edit_form');
			$this->setTitle(Mage::helper('bannerads')->__('Block Information'));
		}

		protected function _beforeToHtml()
		{
			$this->addTab('form_section',
			 array(
			  'label' => Mage::helper('bannerads')->__('Block Information'),
			  'title' => Mage::helper('bannerads')->__('Block Information'),
			  'content' => $this->getLayout()->createBlock('bannerads/adminhtml_bannerads_edit_tab_main')->toHtml(),));
			$this->addTab('form_banner_images',
			 array(
			  'label' => Mage::helper('bannerads')->__('Select Images'),
			  'title' => Mage::helper('bannerads')->__('Select Images'),
			  'class' => 'ajax',
			  'url' => $this->getUrl('adminhtml/bannerads/imagelist', array('_current' => TRUE, 'id' => $this->getRequest()->getParam('id')))
			 ));
			$this->addTab('categories',
			 array(
			  'label' => Mage::helper('bannerads')->__('Associated Category'),
			  'title' => Mage::helper('bannerads')->__('Associated Category'),
			  'class' => 'ajax',
			  'url'   => $this->getUrl('*/*/categories', array('_current' => true)),
			 ));

			return parent::_beforeToHtml();
		}
	}