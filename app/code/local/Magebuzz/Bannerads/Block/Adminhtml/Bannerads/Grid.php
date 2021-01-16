<?php

	/*
	* Copyright (c) 2015 www.magebuzz.com
	*/

	class Magebuzz_Bannerads_Block_Adminhtml_Bannerads_Grid extends Mage_Adminhtml_Block_Widget_Grid
	{
		public function __construct()
		{
			parent::__construct();
			$this->setId('banneradsGrid');
			$this->setUseAjax(TRUE);
			$this->setDefaultSort('block_id');
			$this->setDefaultDir('DESC');
			$this->setSaveParametersInSession(TRUE);
		}

		protected function _prepareCollection()
		{
			$collection = Mage::getModel('bannerads/bannerads')->getCollection();

			$this->setCollection($collection);
			return parent::_prepareCollection();
		}

		protected function _prepareColumns()
		{
			$this->addColumn('block_id', array(
			 'header' => Mage::helper('bannerads')->__('ID'),
			 'align' => 'right',
			 'width' => '50px',
			 'type' => 'number',
			 'index' => 'block_id',
			));

			$this->addColumn('block_title', array(
			 'header' => Mage::helper('bannerads')->__('Block Title'),
			 'align' => 'left',
			 'type' => 'text',
			 'index' => 'block_title',
			));


			$this->addColumn('block_position', array(
			 'header' => Mage::helper('bannerads')->__('Block Position'),
			 'align' => 'left',
			 'type' => 'text',
			 'width' => '80px',
			 'index' => 'block_position',
			));

			$displayType = Magebuzz_Bannerads_Model_Displaytype::getOptionArray();
			$this->addColumn('display_type', array(
			 'header' => Mage::helper('bannerads')->__('Display Type'),
			 'align' => 'left',
			 'index' => 'display_type',
			 'type' => 'options',
			 'options' => $displayType
			));

			$this->addColumn('from_date', array(
			 'header' => Mage::helper('bannerads')->__('From Date'),
			 'align' => 'left',
			 'index' => 'from_date',
			 'type' => 'datetime',
			));

			$this->addColumn('to_date', array(
			 'header' => Mage::helper('bannerads')->__('To Date'),
			 'align' => 'left',
			 'index' => 'to_date',
			 'type' => 'datetime',
			));

			$this->addColumn('customer_group_ids', array(
			 'header' => Mage::helper('bannerads')->__('Customer Group'),
			 'align' => 'left', 'index' => 'customer_group_ids',
			 'renderer' => 'Magebuzz_Bannerads_Block_Adminhtml_Bannerads_Renderer_Customergroup',
			));


			$this->addColumn('sort_order', array(
			 'header' => Mage::helper('bannerads')->__('Sort Order'),
			 'align' => 'left',
			 'type' => 'number',
			 'index' => 'sort_order',
			));

			$this->addColumn('status', array(
			 'header' => Mage::helper('bannerads')->__('Status'),
			 'align' => 'left', 'width' => '80px',
			 'index' => 'status', 'type' => 'options',
			 'options' => array(
			  1 => 'Enabled',
			  2 => 'Disabled',
			 ),
			));

			$this->addColumn('action', array(
			 'header' => Mage::helper('bannerads')->__('Action'),
			 'width' => '100',
			 'type' => 'action',
			 'getter' => 'getId',
			 'actions' => array(
			  array(
			   'caption' => Mage::helper('bannerads')->__('Edit'),
			   'url' => array(
				'base' => '*/*/edit'
			   ),
			   'field' => 'id'
			  )),
			 'filter' => FALSE,
			 'sortable' => FALSE,
			 'index' => 'stores',
			 'is_system' => TRUE,
			));

			$this->addExportType('*/*/exportCsv', Mage::helper('bannerads')->__('CSV'));
			$this->addExportType('*/*/exportXml', Mage::helper('bannerads')->__('XML'));

			return parent::_prepareColumns();
		}

		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('block_id');
			$this->getMassactionBlock()->setFormFieldName('bannerads');

			$this->getMassactionBlock()->addItem('delete', array(
			 'label' => Mage::helper('bannerads')->__('Delete'),
			 'url' => $this->getUrl('*/*/massDelete'),
			 'confirm' => Mage::helper('bannerads')->__('Are you sure?')
			));

			$statuses = Mage::getSingleton('bannerads/status')->getOptionArray();

			array_unshift($statuses, array('label' => '', 'value' => ''));
			$this->getMassactionBlock()->addItem('status', array(
			 'label' => Mage::helper('bannerads')->__('Change status'),
			 'url' => $this->getUrl('*/*/massStatus', array('_current' => TRUE)),
			 'additional' => array(
			  'visibility' => array(
			   'name' => 'status',
			   'type' => 'select',
			   'class' => 'required-entry',
			   'label' => Mage::helper('bannerads')->__('Status'),
			   'values' => $statuses
			  )
			 )
			));
			return $this;
		}

		public function getRowUrl($row)
		{
			return $this->getUrl('*/*/edit', array('id' => $row->getId()));
		}

		public function getGridUrl()
		{
			return $this->getUrl('*/*/grid', array('_current' => TRUE));
		}

	}
