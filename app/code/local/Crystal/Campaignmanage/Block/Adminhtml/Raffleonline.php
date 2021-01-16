<?php

class Crystal_Campaignmanage_Block_Adminhtml_Raffleonline extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct() {
		$this->_controller = 'adminhtml_raffleonline';
		$this->_blockGroup = 'campaignmanage';
		$this->_headerText = Mage::helper('campaignmanage')->__('Manage Raffle Online');
		$this->_addButton('add_btn', array(
			'label'     => Mage::helper('campaignmanage')->__('New Raffle'),
			'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/new/') .'\')',
			'class'     => 'add'
		));
		parent::__construct();
		$this->_removeButton('add');
	}
}