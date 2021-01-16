<?php
/*
* Copyright (c) 2017 www.tigren.com 
*/
class Tigren_Ruffle_Block_Adminhtml_Ruffle_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
  	public function __construct() {
		parent::__construct();
		$this->setId('ruffle_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('ruffle')->__('Prize Information'));
  	}

  	protected function _beforeToHtml() {
		$this->addTab('form_section', array(
			'label'     => Mage::helper('ruffle')->__('Prize Information'),
			'title'     => Mage::helper('ruffle')->__('Prize Information'),
			'content'   => $this->getLayout()->createBlock('ruffle/adminhtml_ruffle_edit_tab_form')->toHtml(),
		));

		$this->addTab('products', array(
            'label'     => Mage::helper('catalog')->__('Prize Items'),
            'url'       => $this->getUrl('*/*/product', array('_current' => true, 'id' => $this->getRequest()->getParam('id'))),
            'class'     => 'ajax',
        ));

        $this->addTab('allmember', array(
            'label'     => Mage::helper('catalog')->__('Subscribe All member List'),
            'url'       => $this->getUrl('*/*/allmember', array('_current' => true, 'id' => $this->getRequest()->getParam('id'))),
            'class'     => 'ajax',
        ));

        // $this->addTab('vip', array(
        //     'label'     => Mage::helper('catalog')->__('Subscribe VIP List'),
        //     'url'       => $this->getUrl('*/*/vip', array('_current' => true, 'id' => $this->getRequest()->getParam('id'))),
        //     'class'     => 'ajax',
        // ));

        // $this->addTab('member', array(
        //     'label'     => Mage::helper('catalog')->__('Subscribe Member List'),
        //     'url'       => $this->getUrl('*/*/member', array('_current' => true, 'id' => $this->getRequest()->getParam('id'))),
        //     'class'     => 'ajax',
        // ));

        $this->addTab('winner', array(
            'label'     => Mage::helper('catalog')->__('Assign Winner'),
            'url'       => $this->getUrl('*/*/winner', array('_current' => true, 'id' => $this->getRequest()->getParam('id'))),
            'class'     => 'ajax',
        ));
	 
		return parent::_beforeToHtml();
  	}
}