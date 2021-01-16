<?php
/*
* Copyright (c) 2013 www.magebuzz.com 
*/
class Magebuzz_Countingdown_Block_Adminhtml_Countingdown_Grid extends Mage_Adminhtml_Block_Widget_Grid {
  public function __construct() {
		parent::__construct();
		$this->setId('countingdownGrid');
		$this->setDefaultSort('countingdown_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection() {
		$collection = Mage::getModel('countingdown/countingdown')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
  }

  protected function _prepareColumns() {
		$this->addColumn('countingdown_id', array(
			'header'    => Mage::helper('countingdown')->__('ID'),
			'align'     =>'right',
			'width'     => '50px',
			'index'     => 'countingdown_id',
		));

		$this->addColumn('title', array(
			'header'    => Mage::helper('countingdown')->__('Title'),
			'align'     =>'left',
			'index'     => 'title',
		));

		$this->addColumn('status', array(
			'header'    => Mage::helper('countingdown')->__('Status'),
			'align'     => 'left',
			'width'     => '80px',
			'index'     => 'status',
			'type'      => 'options',
			'options'   => array(
				1 => 'Enabled',
				2 => 'Disabled',
			),
		));
	  
		$this->addColumn('action',
			array(
				'header'    =>  Mage::helper('countingdown')->__('Action'),
				'width'     => '100',
				'type'      => 'action',
				'getter'    => 'getId',
				'actions'   => array(
					array(
						'caption'   => Mage::helper('countingdown')->__('Edit'),
						'url'       => array('base'=> '*/*/edit'),
						'field'     => 'id'
					)
				),
				'filter'    => false,
				'sortable'  => false,
				'index'     => 'stores',
				'is_system' => true,
		));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('countingdown')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('countingdown')->__('XML'));
	  
    return parent::_prepareColumns();
  }

	protected function _prepareMassaction() {
		$this->setMassactionIdField('countingdown_id');
		$this->getMassactionBlock()->setFormFieldName('countingdown');

		$this->getMassactionBlock()->addItem('delete', array(
			'label'    => Mage::helper('countingdown')->__('Delete'),
			'url'      => $this->getUrl('*/*/massDelete'),
			'confirm'  => Mage::helper('countingdown')->__('Are you sure?')
		));

		$statuses = Mage::getSingleton('countingdown/status')->getOptionArray();

		array_unshift($statuses, array('label'=>'', 'value'=>''));
		$this->getMassactionBlock()->addItem('status', array(
			'label'=> Mage::helper('countingdown')->__('Change status'),
			'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
			'additional' => array(
				'visibility' => array(
					'name' => 'status',
					'type' => 'select',
					'class' => 'required-entry',
					'label' => Mage::helper('countingdown')->__('Status'),
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