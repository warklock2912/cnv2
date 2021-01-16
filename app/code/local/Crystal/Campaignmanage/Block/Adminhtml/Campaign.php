<?php

class Crystal_Campaignmanage_Block_Adminhtml_Campaign extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct() {
		$id = $this->getRequest()->getParam('id');
		$this->_controller = 'adminhtml_campaign';
		$this->_blockGroup = 'campaignmanage';
		$this->_headerText = Mage::helper('campaignmanage')->__('Manage Campaigns');
	    $this->_addButton('add_btn', array(
        		'label'     => Mage::helper('campaignmanage')->__('New Campaign'),
        		'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/new/locator_id/'.$id) .'\')',
        		'class'     => 'add'
        ));
		parent::__construct();
		$this->_removeButton('add');
	}
}