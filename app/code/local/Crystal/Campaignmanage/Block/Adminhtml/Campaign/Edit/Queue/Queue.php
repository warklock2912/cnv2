<?php

class Crystal_Campaignmanage_Block_Adminhtml_Campaign_Edit_Queue_Queue extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('campaignAllUserGrid');
//		$this->setUseAjax(true);
		$this->setDefaultSort('no_of_queue');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(TRUE);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('campaignmanage/queue')->getCollection();
		$collection->addFieldToFilter('campaign_id', $this->getRequest()->getParam('id'))
			->addFieldToFilter('queue_status',1);
		;
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('no_of_queue', array(
			'header'    => Mage::helper('campaignmanage')->__('No. '),
			'sortable'  => true,
			'width'     => 60,
			'index'     => 'no_of_queue'
		));
		$this->addColumn('customer_id', array(
			'header'    => Mage::helper('campaignmanage')->__('Customer ID'),
			'sortable'  => false,
			'index'     => 'customer_id'
		));
		$this->addColumn('customer_name', array(
			'header'    => Mage::helper('campaignmanage')->__('Name'),
			'sortable'  => false,
			'index'     => 'customer_name'
		));
		$this->addColumn('email', array(
			'header'    => Mage::helper('campaignmanage')->__('Email'),
			'sortable'  => false,
			'index'     => 'email'
		));
		$this->addColumn('card_id', array(
			'header'    => Mage::helper('campaignmanage')->__('Personal ID'),
			'sortable'  => false,
			'index'     => 'card_id'
		));
		$this->addColumn('phone', array(
			'header'    => Mage::helper('campaignmanage')->__('Phone Number'),
			'sortable'  => false,
			'index'     => 'phone'
		));
		$this->addColumn('created_at', array(
			'header'    => Mage::helper('campaignmanage')->__('Created At'),
			'sortable'  => true,
			'index'     => 'created_at',
			'type' => 'datetime'
		));
		$this->addExportType('*/*/exportCsv', Mage::helper('campaignmanage')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('campaignmanage')->__('XML'));

		return parent::_prepareColumns();
	}
//	public function getGridUrl()
//	{
//		return $this->getUrl('*/*/grid', array('_current'=> true));
//	}
}