<?php
/*
* Copyright (c) 2017 www.tigren.com 
*/
class Tigren_Ruffle_Block_Adminhtml_Ruffle_Grid extends Mage_Adminhtml_Block_Widget_Grid {
  	public function __construct() {
		parent::__construct();
		$this->setId('ruffleGrid');
		$this->setDefaultSort('ruffle_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
  	}

  	protected function _prepareCollection() {
		$collection = Mage::getModel('ruffle/ruffle')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
  	}

  	protected function _prepareColumns() {
		$this->addColumn('ruffle_id', array(
			'header'    => Mage::helper('ruffle')->__('ID'),
			'align'     =>'right',
			'width'     => '50px',
			'index'     => 'ruffle_id',
		));

		$this->addColumn('title', array(
			'header'    => Mage::helper('ruffle')->__('Title'),
			'align'     =>'left',
			'index'     => 'title',
		));

		$this->addColumn('start_date', array(
			'header'    => Mage::helper('ruffle')->__('Start Date'),
			'align'     =>'left',
			'index'     => 'start_date',
			'type'		=> 'date'
		));

		$this->addColumn('end_date', array(
			'header'    => Mage::helper('ruffle')->__('End Date'),
			'align'     =>'left',
			'index'     => 'end_date',
			'type'		=> 'date'
		));

		$this->addColumn('is_active', array(
			'header'    => Mage::helper('ruffle')->__('Status'),
			'align'     => 'left',
			'width'     => '80px',
			'index'     => 'is_active',
			'type'      => 'options',
			'options'   => array(
				1 => 'Enabled',
				2 => 'Disabled',
			),
		));
	  
		$this->addColumn('action',
			array(
				'header'    =>  Mage::helper('ruffle')->__('Action'),
				'width'     => '100',
				'type'      => 'action',
				'getter'    => 'getId',
				'actions'   => array(
					array(
						'caption'   => Mage::helper('ruffle')->__('Edit'),
						'url'       => array('base'=> '*/*/edit'),
						'field'     => 'id'
					)
				),
				'filter'    => false,
				'sortable'  => false,
				'index'     => 'stores',
				'is_system' => true,
		));
		
		// $this->addExportType('*/*/exportCsv', Mage::helper('ruffle')->__('CSV'));
		// $this->addExportType('*/*/exportXml', Mage::helper('ruffle')->__('XML'));
	  
    	return parent::_prepareColumns();
  	}

	protected function _prepareMassaction() {
		$this->setMassactionIdField('ruffle_id');
		$this->getMassactionBlock()->setFormFieldName('ruffle');

		$this->getMassactionBlock()->addItem('delete', array(
			'label'    => Mage::helper('ruffle')->__('Delete'),
			'url'      => $this->getUrl('*/*/massDelete'),
			'confirm'  => Mage::helper('ruffle')->__('Are you sure?')
		));

		$statuses = Mage::getSingleton('ruffle/status')->getOptionArray();

		array_unshift($statuses, array('label'=>'', 'value'=>''));
		$this->getMassactionBlock()->addItem('status', array(
			'label'=> Mage::helper('ruffle')->__('Change status'),
			'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
			'additional' => array(
				'visibility' => array(
					'name' => 'status',
					'type' => 'select',
					'class' => 'required-entry',
					'label' => Mage::helper('ruffle')->__('Status'),
					'values' => $statuses
				)
			)
		));
		return $this;
	}

  	public function getRowUrl($row) {
    	return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  	}
}