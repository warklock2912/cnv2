<?php

class Crystal_Campaignmanage_Block_Adminhtml_Raffleonline_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('raffleOnlineGrid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(TRUE);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('campaignmanage/campaignonline')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('id', array(
			'header' => Mage::helper('campaignmanage')->__('ID'),
			'align' => 'right',
			'width' => '50px',
			'index' => 'id',
		));

		$this->addColumn('campaign_name', array(
			'header' => Mage::helper('campaignmanage')->__('Campaign Name'),
			'align' => 'left',
			'index' => 'campaign_name',
		));
		$this->addColumn('no_of_part', array(
			'header' => Mage::helper('campaignmanage')->__('No Of Participants'),
			'align' => 'left',
			'index' => 'no_of_part',
		));

		$this->addColumn('start_register_time', array(
			'header' => Mage::helper('campaignmanage')->__('Start Register Time'),
			'align' => 'left',
			'type' => 'datetime',
			'index' => 'start_register_time'
		));
		$this->addColumn('end_register_time', array(
			'header' => Mage::helper('campaignmanage')->__('End Register Time'),
			'align' => 'left',
			'type' => 'datetime',
			'index' => 'end_register_time'
		));


		$this->addColumn('app_display', array(
			'header' => Mage::helper('campaignmanage')->__('Display On App?'),
			'align' => 'left',
			'index' => 'app_display',
			'type' => 'options',
			'options' => array(1 => 'Enabled', 0 => 'Disabled',),
		));


		$this->addColumn('action', array(
			'header' => Mage::helper('campaignmanage')->__('Action'),
			'width' => '100',
			'type' => 'action',
			'getter' => 'getId',
			'actions' => array(
				array('caption' => Mage::helper('campaignmanage')->__('Edit'), 'url' => array('base' => '*/*/edit'), 'field' => 'id')
			),
			'filter' => FALSE,
			'sortable' => FALSE,
			'index' => 'stores',
			'is_system' => TRUE,
		));

		$this->addExportType('*/*/exportCsv', Mage::helper('campaignmanage')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('campaignmanage')->__('XML'));

		return parent::_prepareColumns();
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('campaign_id');
		$this->getMassactionBlock()->setFormFieldName('campaignmanage');

		$this->getMassactionBlock()->addItem('delete', array(
			'label' => Mage::helper('adminhtml')->__('Delete'),
			'url' => $this->getUrl('*/*/massDelete'),
			'confirm' => Mage::helper('adminhtml')->__('Are you sure?')
		));

		return $this;
	}

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
}