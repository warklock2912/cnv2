<?php
/*
* Copyright (c) 2015 www.magebuzz.com 
*/
class Magebuzz_Customaddress_Block_Adminhtml_Subdistrict_Grid extends Mage_Adminhtml_Block_Widget_Grid {
  public function __construct() {
		parent::__construct();
		$this->setId('cityGrid');
		$this->setDefaultSort('subdistrict_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection() {
		$collection = Mage::getModel('customaddress/subdistrict')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
  }

  protected function _prepareColumns() {
		$this->addColumn('subdistrict_id', array(
			'header'    => Mage::helper('customaddress')->__('ID'),
			'align'     =>'right',
			'width'     => '50px',
			'index'     => 'subdistrict_id',
		));
		
		$this->addColumn('code', array(
			'header'           => Mage::helper('customaddress')->__('Code'),
			'align'            => 'left',
			'width'            => '110px',
			'index'            => 'code',
			//'editable' =>true,
			'column_css_class' => 'code_td'
		));
		
		$this->addColumn('default_name', array(
			'header'           => Mage::helper('customaddress')->__('Default Name'),
			'align'            => 'left',
			'width'            => '110px',
			'index'            => 'default_name',
			//'editable' =>true,
			'column_css_class' => 'default_name'
		));
		
		$this->addColumn('zipcode', array(
			'header'           => Mage::helper('customaddress')->__('Zip Code'),
			'align'            => 'left',
			'width'            => '110px',
			'index'            => 'zipcode'
		));
	  
		$this->addColumn('action',
			array(
				'header'    =>  Mage::helper('customaddress')->__('Action'),
				'width'     => '100',
				'type'      => 'action',
				'getter'    => 'getId',
				'actions'   => array(
					array(
						'caption'   => Mage::helper('customaddress')->__('Edit'),
						'url'       => array('base'=> '*/*/edit'),
						'field'     => 'id'
					)
				),
				'filter'    => false,
				'sortable'  => false,
				'index'     => 'stores',
				'is_system' => true,
		));
		
		//$this->addExportType('*/*/exportCsv', Mage::helper('customaddress')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('customaddress')->__('XML'));
	  
    return parent::_prepareColumns();
  }

	protected function _prepareMassaction() {
		$this->setMassactionIdField('subdistrict_id');
		$this->getMassactionBlock()->setFormFieldName('subdistrict');

		$this->getMassactionBlock()->addItem('delete', array(
			'label'    => Mage::helper('customaddress')->__('Delete'),
			'url'      => $this->getUrl('*/*/massDelete'),
			'confirm'  => Mage::helper('customaddress')->__('Are you sure?')
		));
		
		return $this;
	}

  public function getRowUrl($row) {
    return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
}