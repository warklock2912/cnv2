<?php
class Crystal_Pushnotification_Block_Adminhtml_Pushnotification extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct() {
		$this->_controller = 'adminhtml_pushnotification';
		$this->_blockGroup = 'pushnotification';
		$this->_headerText = Mage::helper('pushnotification')->__('Manage Notification');
		parent::__construct();
	}
}