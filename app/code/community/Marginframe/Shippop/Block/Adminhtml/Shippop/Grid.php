<?php
/*
* Copyright (c) 2018 Margin Frame Arrang
*/
class Marginframe_Shippop_Block_Adminhtml_Shippop_Grid extends Mage_Adminhtml_Block_Widget_Grid {
  	public function __construct() {
		parent::__construct();
		$this->setId('shippopGrid');
		$this->setDefaultSort('created_at');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);

		
  	}

  	protected function _prepareCollection() {
		$collection = Mage::getModel('shippop/shippop')->getCollection();
		$collection->getSelect()->group('shippop_purchase_id');

		$this->setCollection($collection);

		return parent::_prepareCollection();
  	}

  	protected function _prepareColumns() {
		
		$this->addColumn('shippop_purchase_id', array(
			'header'    => Mage::helper('shippop')->__('#Purchase ID'),
			'align'     =>'left',
			'width'     => '150px',
			'index'     => 'shippop_purchase_id',
		));
		$this->addColumn('created_at', array(
			'header'    => Mage::helper('shippop')->__('Date'),
			'align'     =>'left',
			'width'     => '150px',
			'index'     => 'created_at',
		));
		$this->addColumn('action',
			array(
				'header'    =>  Mage::helper('shippop')->__('Action'),
				'width'     => '20',
				'type'      => 'action',
				'getter'    => 'getShippopPurchaseId',
				'actions'   => array(
					array(
						'caption'   => Mage::helper('shippop')->__('Print'),
						'url'       => array('base'=> '*/*/print'),
						'field'     => 'shippop_purchase_id'
					)
				),
				'filter'    => false,
				'sortable'  => false,
				'index'     => 'stores',
				'is_system' => true,
		));

		
		
		// $this->addExportType('*/*/exportCsv', Mage::helper('shippop')->__('CSV'));
		// $this->addExportType('*/*/exportXml', Mage::helper('shippop')->__('XML'));
	  
    	return parent::_prepareColumns();
  	}

	// protected function _prepareMassaction() {
	// 	$this->setMassactionIdField('shippop_id');
	// 	$this->getMassactionBlock()->setFormFieldName('shippop');

	// 	$this->getMassactionBlock()->addItem('delete', array(
	// 		'label'    => Mage::helper('shippop')->__('Delete'),
	// 		'url'      => $this->getUrl('*/*/massDelete'),
	// 		'confirm'  => Mage::helper('shippop')->__('Are you sure?')
	// 	));

	// 	//$statuses = Mage::getSingleton('shippop/status')->getOptionArray();

	// 	array_unshift($statuses, array('label'=>'', 'value'=>''));
	// 	$this->getMassactionBlock()->addItem('status', array(
	// 		'label'=> Mage::helper('shippop')->__('Change status'),
	// 		'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
	// 		'additional' => array(
	// 			'visibility' => array(
	// 				'name' => 'status',
	// 				'type' => 'select',
	// 				'class' => 'required-entry',
	// 				'label' => Mage::helper('shippop')->__('Status'),
	// 				'values' => array()
	// 			)
	// 		)
	// 	));
	// 	return $this;
	// }

  	// public function getRowUrl($row) {
   //  	return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  	// }
}