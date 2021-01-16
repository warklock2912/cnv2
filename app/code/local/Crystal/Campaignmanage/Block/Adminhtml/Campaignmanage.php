<?php

class Crystal_Campaignmanage_Block_Adminhtml_Campaignmanage extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct() {

		$this->_controller = 'adminhtml_campaignmanage';
		$this->_blockGroup = 'campaignmanage';
		$this->_headerText = Mage::helper('campaignmanage')->__('Manage Campaigns');
		$this->_addButtonLabel = Mage::helper('campaignmanage')->__('Add campaignmanage');


		$this->_addButton('button2', array(
			'label'     => Mage::helper('campaignmanage')->__('Manage Dealer'),
			'onclick'   => 'setLocation(\'' . $this->getUrl('*/dealerlocator') .'\')',
		));
		parent::__construct();
		$this->removeButton('add');
	}
}