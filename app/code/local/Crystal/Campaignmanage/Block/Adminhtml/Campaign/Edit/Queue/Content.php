<?php

class Crystal_Campaignmanage_Block_Adminhtml_Campaign_Edit_Queue_Content extends Mage_Adminhtml_Block_Widget
	implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('campaign_queue_content');
		$this->setTemplate('campaignmanage/content.phtml');
	}

	protected function _prepareLayout()
	{
		$this->setChild('next_queue_button',
			$this->getLayout()->createBlock('adminhtml/widget_button')
				->setData(array(
					'label' => Mage::helper('campaignmanage')->__('Next Customer'),
					'name' => 'next_queue',
					'element_name' => 'next_queue',
					'class' => 'next_queue',
				))
		);
		$this->setChild('start_queue_button',
			$this->getLayout()->createBlock('adminhtml/widget_button')
				->setData(array(
					'label' => Mage::helper('campaignmanage')->__('Start Queue'),
					'name' => 'start_queue',
					'element_name' => 'start_queue',
					'class' => 'start_queue',
				))
		);
		return parent::_prepareLayout();
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function checkShuffle()
	{
		$campaign = Mage::getModel('campaignmanage/campaign')->load($this->getRequest()->getParam('id'));
		$campaignType = $campaign->getCampaignType();

		if ($campaignType == Crystal_Campaignmanage_Model_Campaigntype::TYPE_STORE_SHUFFLE && $campaign->getIsWaiting() != false )
			return true;
		else
			return false;
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */

	public function getCampaignId()
	{
		return $this->getRequest()->getParam('id');
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */
	public function getCampaignStatus()
	{
		return Mage::getModel('campaignmanage/campaign')->load($this->getRequest()->getParam('id'))->getStatus();
	}

	public function getCurrentCustomer()
	{
		$currentCustomer = Mage::getModel('campaignmanage/queue')->getCollection()
			->addFieldToFilter('campaign_id', $this->getRequest()->getParam('id'))
			->addFieldToFilter('queue_status', 2)
			->getFirstItem();
		return $currentCustomer;
	}

	/**
	 * @return string
	 */
	public function getNextQueueButtonHtml()
	{
		return $this->getChildHtml('next_queue_button');
	}

	/**
	 * @return string
	 */
	public function getStartQueueButtonHtml()
	{
		return $this->getChildHtml('start_queue_button');
	}

	/**
	 * @return string
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