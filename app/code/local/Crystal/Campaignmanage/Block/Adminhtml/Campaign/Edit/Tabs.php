<?php

class Crystal_Campaignmanage_Block_Adminhtml_Campaign_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('form_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('campaignmanage')->__('Campaign Infomation'));
	}

	public function getCampaignType()
	{
		$campaign = Mage::getModel('campaignmanage/campaign')->load($this->getRequest()->getParam('id'));
		return $campaign->getCampaignType();
	}

	public function _beforeToHtml()
	{
		$this->addTab('form_session', array(
			'label' => Mage::helper('campaignmanage')->__('Campaign Detail'),
			'title' => Mage::helper('campaignmanage')->__('Campaign Detail'),
			'content' => $this->getLayout()->createBlock('campaignmanage/adminhtml_campaign_edit_tab_form')->toHtml(),
		));

		$typeStoreRaffle = Crystal_Campaignmanage_Model_Campaigntype::TYPE_STORE_RAFFLE;
		$typeStoreQueue = Crystal_Campaignmanage_Model_Campaigntype::TYPE_STORE_QUEUE;
		$typeStoreShuffle = Crystal_Campaignmanage_Model_Campaigntype::TYPE_STORE_SHUFFLE;

		if ($this->getCampaignType() == $typeStoreRaffle) {
			$this->addTab('item', array(
				'label' => Mage::helper('campaignmanage')->__('Item(s)'),
				'url' => $this->getUrl('*/*/product', array('_current' => true, 'id' => $this->getRequest()->getParam('id'))),
				'class' => 'ajax',
			));
			$this->addTab('campaign_raffle_member', array(
				'label' => Mage::helper('campaignmanage')->__('Subcribe All Member List'),
				'title' => Mage::helper('campaignmanage')->__('Subcribe All Member List'),
				'content' => $this->getLayout()->createBlock('campaignmanage/adminhtml_campaign_edit_raffle_member')->toHtml(),
			));
			$this->addTab('campaign_raffle', array(
				'label' => Mage::helper('campaignmanage')->__('Raffle'),
				'title' => Mage::helper('campaignmanage')->__('Raffle'),
				'content' => $this->getLayout()->createBlock('campaignmanage/adminhtml_campaign_edit_raffle_raffle')->toHtml(),
			));

		}
		if ($this->getCampaignType() == $typeStoreQueue || $this->getCampaignType() == $typeStoreShuffle) {
			$this->addTab('campaign_queue', array(
				'label' => Mage::helper('campaignmanage')->__('Queue'),
				'title' => Mage::helper('campaignmanage')->__('Queue'),
				'content' => $this->getLayout()->createBlock('campaignmanage/adminhtml_campaign_edit_tab_queue')->toHtml(),
			));
			$this->addTab('campaign_member', array(
				'label' => Mage::helper('campaignmanage')->__('Subcribe All Member List'),
				'title' => Mage::helper('campaignmanage')->__('Subcribe All Member List'),
				'content' => $this->getLayout()->createBlock('campaignmanage/adminhtml_campaign_edit_tab_allmember')->toHtml(),
			));
		}
		return parent::_beforeToHtml();
	}
}