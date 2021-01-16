<?php

class Crystal_BlockSlide_Block_Adminhtml_Blockslide_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('blockslideGrid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(TRUE);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('blockslide/blockslide')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('id', array(
			'header' => Mage::helper('blockslide')->__('ID'),
			'align' => 'right',
			'width' => '50px',
			'index' => 'id',
		));
		$this->addColumn('image', array(
			'header' => Mage::helper('blockslide')->__('Image'),
			'index' => 'image',
			'align' => 'center',
			'filter' => false,
			'renderer' => 'Crystal_BlockSlide_Block_Adminhtml_Blockslide_Renderer_Images'
		));

		$displayType = Crystal_BlockSlide_Model_Status::getOptionArray();
		$this->addColumn('status', array(
			'header' => Mage::helper('blockslide')->__('Status'),
			'align' => 'left',
			'index' => 'status',
			'type' => 'options',
			'width' => '100px',
			'options' => $displayType
		));
		$this->addColumn('position', array(
			'header' => Mage::helper('blockslide')->__('Position'),
			'align' => 'right',
			'width' => '100px',
			'index' => 'position',
		));
		$this->addColumn('url', array(
			'header' => Mage::helper('blockslide')->__('URL'),
			'align' => 'right',
			'index' => 'url',
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