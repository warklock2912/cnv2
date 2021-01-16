<?php

class Crystal_Campaignmanage_Block_Adminhtml_Campaign_Edit_Raffle_Member extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('raffleAllUserGrid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(TRUE);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('campaignmanage/raffle')->getCollection();
		$collection->addFieldToFilter('campaign_id', $this->getRequest()->getParam('id'));
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareLayout()
	{
		parent::_prepareLayout();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('id', array(
			'header' => Mage::helper('campaignmanage')->__('ID'),
			'sortable' => true,
			'width' => 60,
			'index' => 'id'
		));
		$this->addColumn('product_id', array(
			'header' => Mage::helper('campaignmanage')->__('Product ID'),
			'sortable' => false,
			'index' => 'product_id'
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
		$this->addColumn('is_winner', array(
			'header' => Mage::helper('campaignmanage')->__('Is Winner?'),
			'sortable' => true,
			'index' => 'is_winner'
		));
		$this->addColumn('created_at', array(
			'header' => Mage::helper('campaignmanage')->__('Created At'),
			'sortable' => false,
			'index' => 'created_at',
			'type' => 'datetime'
		));
        $this->addExportType('*/*/exportRaffleMemberCsv', Mage::helper('campaignmanage')->__('CSV'));
        $this->addExportType('*/*/exportRaffleMemberXml', Mage::helper('campaignmanage')->__('XML'));

		return parent::_prepareColumns();
	}
}
