<?php

/**
 * Class Crystal_Campaignmanage_Block_Adminhtml_Campaign_Edit_Tab_Queue
 */
class Crystal_Campaignmanage_Block_Adminhtml_Campaign_Edit_Tab_Queue extends Mage_Adminhtml_Block_Widget
	implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
	/**
	 * Crystal_Campaignmanage_Block_Adminhtml_Campaign_Edit_Tab_Queue constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setId('campaign_queue_process');
		$this->setTemplate('campaignmanage/queue.phtml');
	}



	/**
	 * @return Mage_Core_Block_Abstract
	 */
	protected function _prepareLayout()
	{
		return parent::_prepareLayout();
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */
	public function getTabLabel()
	{
		return Mage::helper('campaignmanage')->__('Queue');
	}

	/**
	 * @return string
	 */
	public function getTabTitle()
	{
		return Mage::helper('campaignmanage')->__('Queue');
	}

	/**
	 * @return bool
	 */
	public function canShowTab()
	{
		return true;
	}

	/**
	 * @return bool
	 */
	public function isHidden()
	{
		return false;
	}
}