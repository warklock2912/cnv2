<?php

class Crystal_Campaignmanage_Block_Adminhtml_Campaign_Edit_Tab_Allmember extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('campaignAllUserGrid');
		$this->setDefaultSort('no_of_queue');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(TRUE);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('campaignmanage/queue')->getCollection();
		$collection->addFieldToFilter('campaign_id', $this->getRequest()->getParam('id'));
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareLayout()
	{
		parent::_prepareLayout();
		$this->setChild('shuffle_button',
			$this->getLayout()->createBlock('adminhtml/widget_button')
				->setData(array(
					'label' => Mage::helper('adminhtml')->__('Shuffle'),
					'onclick' => 'setLocation(\' ' . $this->getUrl('*/*/shuffle', array('_secure' => true, 'id' => $this->getRequest()->getParam('id'))) . '\')',
				))
		);
	}

	public function getShuffleButtonHtml()
	{
		return $this->getChildHtml('shuffle_button');
	}

	public function getMainButtonsHtml()
	{
		$campaign = Mage::getModel('campaignmanage/campaign')->load($this->getRequest()->getParam('id'));
		$typeShuffle = Crystal_Campaignmanage_Model_Campaigntype::TYPE_STORE_SHUFFLE;
		$html = '';
		if ($campaign->getCampaignType() == $typeShuffle && $campaign->getIsWaiting() == true)
			$html = $this->getShuffleButtonHtml();
		$html .= parent::getMainButtonsHtml();
		return $html;
	}

	protected function _prepareColumns()
	{
		$this->addColumn('no_of_queue', array(
			'header' => Mage::helper('campaignmanage')->__('No.'),
			'sortable' => true,
			'width' => 60,
			'index' => 'no_of_queue'
		));
		$this->addColumn('customer_id', array(
			'header' => Mage::helper('campaignmanage')->__('Customer ID'),
			'sortable' => false,
			'index' => 'customer_id'
		));
		$this->addColumn('customer_name', array(
			'header' => Mage::helper('campaignmanage')->__('Name'),
			'sortable' => false,
			'index' => 'customer_name'
		));
		$this->addColumn('email', array(
			'header' => Mage::helper('campaignmanage')->__('Email'),
			'sortable' => false,
			'index' => 'email'
		));
		$this->addColumn('phone', array(
			'header' => Mage::helper('campaignmanage')->__('Phone Number'),
			'sortable' => false,
			'index' => 'phone'
		));
		$this->addColumn('card_id', array(
			'header' => Mage::helper('campaignmanage')->__('Personal ID'),
			'sortable' => false,
			'index' => 'card_id'
		));
		$this->addColumn('created_at', array(
			'header' => Mage::helper('campaignmanage')->__('Created At'),
			'sortable' => false,
			'index' => 'created_at',
			'type' => 'datetime'
		));
		$this->addExportType('*/*/exportQueueMemberCsv', Mage::helper('campaignmanage')->__('CSV'));
		$this->addExportType('*/*/exportQueueMemberXml', Mage::helper('campaignmanage')->__('XML'));

		return parent::_prepareColumns();
	}


}
