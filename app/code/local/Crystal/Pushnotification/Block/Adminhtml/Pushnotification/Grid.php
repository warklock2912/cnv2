<?php

class Crystal_Pushnotification_Block_Adminhtml_Pushnotification_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('pushnotificationGrid');
		$this->setDefaultSort('notification_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(TRUE);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('pushnotification/notification')->getCollection()->addFieldToFilter('type', array(
            'neq' => 'crop',
        ));
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{

		$this->addColumn('notification_id', array(
			'header' => Mage::helper('pushnotification')->__('ID'),
			'align' => 'right',
			'width' => '50px',
			'index' => 'notification_id',
		));


		$this->addColumn('notification_date', array(
			'header' => Mage::helper('pushnotification')->__('Date'),
			'align' => 'left',
			'type' => 'datetime',
			'index' => 'notification_date',
		));


		$this->addColumn('url', array(
			'header' => Mage::helper('pushnotification')->__('Url'),
			'align' => 'left',
			'index' => 'url',
		));
		$this->addColumn('title', array(
			'header' => Mage::helper('pushnotification')->__('Title'),
			'align' => 'left',
			'index' => 'title',
		));
		$this->addColumn('message', array(
			'header' => Mage::helper('pushnotification')->__('Message'),
			'align' => 'left',
			'index' => 'message',
		));
        $this->addColumn('is_sent', array(
            'header' => Mage::helper('pushnotification')->__('Is Sent?'),
            'align' => 'left',
            'width'     => '50px',
            'index' => 'is_sent',
        ));
//		$this->addColumn('actions', array(
//			'header'    => Mage::helper('adminnotification')->__('Actions'),
//			'sortable'  => false,
//			'renderer'  => 'Crystal_Pushnotification_Block_Adminhtml_Pushnotification_Renderer_Actions',
//		));

        $this->addColumn('action', array(
            'header' => Mage::helper('pushnotification')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array('caption' => Mage::helper('pushnotification')->__('Edit'), 'url' => array('base' => '*/*/edit'), 'field' => 'id')
            ),
            'filter' => FALSE,
            'sortable' => FALSE,
            'index' => 'stores',
            'is_system' => TRUE,
        ));

		$this->addExportType('*/*/exportCsv', Mage::helper('pushnotification')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('pushnotification')->__('XML'));

		return parent::_prepareColumns();
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('notification_id');
		$this->getMassactionBlock()->setFormFieldName('pushnotification');

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