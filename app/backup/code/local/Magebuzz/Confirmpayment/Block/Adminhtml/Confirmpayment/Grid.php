<?php

class Magebuzz_Confirmpayment_Block_Adminhtml_Confirmpayment_Grid extends Mage_Adminhtml_Block_Widget_Grid {

  public function __construct() {
    parent::__construct();
    $this->setId('test');
    $this->setDefaultSort('form_id');
    $this->setDefaultDir('DESC');
    $this->setSaveParametersInSession(true);
    $this->setUseAjax(true);
  }

  protected function _prepareCollection() {

    $collection = Mage::getModel('confirmpayment/cpform')->getCollection();
    $this->setCollection($collection);
    return parent::_prepareCollection();
  }

  protected function _prepareColumns() {
    $this->addColumn('form_id', array(
        'header' => Mage::helper('confirmpayment')->__('ID'),
        'align' => 'right',
        'width' => '50px',
        'index' => 'form_id',
    ));

    $this->addColumn('order_no', array(
        'header' => Mage::helper('confirmpayment')->__('Order No'),
        'align' => 'left',
        'index' => 'order_no',
        'width' => '400px'
    ));
    $this->addColumn('name', array(
        'header' => Mage::helper('confirmpayment')->__('Name'),
        'align' => 'left',
        'index' => 'name',
        'width' => '200px'
    ));

    $this->addColumn('email', array(
        'header' => Mage::helper('confirmpayment')->__('Email'),
        'index' => 'email',
        'width' => '300px'
    ));

    $this->addColumn('tel', array(
        'header' => Mage::helper('confirmpayment')->__('Telephone'),
        'index' => 'tel',
        'width' => '200px'
    ));

    $this->addColumn('amount', array(
        'header' => Mage::helper('confirmpayment')->__('Baht Amount'),
        'index' => 'amount',
        'width' => '200px'
    ));

    $this->addColumn('bank', array(
        'header' => Mage::helper('confirmpayment')->__('Transferred To Bank'),
        'index' => 'bank',
        'width' => '200px'
    ));

    $this->addColumn('date', array(
        'header' => Mage::helper('confirmpayment')->__('Date Time'),
        'index' => 'date',
        'width' => '250px'
    ));

    $this->addColumn('status', array(
        'header' => Mage::helper('confirmpayment')->__('Status'),
        'align' => 'left',
        'width' => '80px',
        'index' => 'status',
        'type' => 'options',
        'options' => array(
            1 => 'New',
            2 => 'Complete',
        ),
    ));

    $this->addColumn('action', array(
        'header' => Mage::helper('confirmpayment')->__('Action'),
        'width' => '100',
        'type' => 'action',
        'getter' => 'getId',
        'actions' => array(
            array(
                'caption' => Mage::helper('confirmpayment')->__('Delete'),
                'url' => array('base' => '*/*/delete'),
                'field' => 'id'
            )),
        'filter' => false,
        'sortable' => false,
        'index' => 'stores',
        'is_system' => true,
    ));

    //$this->addExportType('*/*/exportCsv', Mage::helper('confirmpayment')->__('CSV'));
    //$this->addExportType('*/*/exportXml', Mage::helper('confirmpayment')->__('XML'));

    return parent::_prepareColumns();
  }

  protected function _prepareMassaction() {
    $this->setMassactionIdField('form_id');
    $this->getMassactionBlock()->setFormFieldName('cpform');

    $this->getMassactionBlock()->addItem('delete', array(
        'label' => Mage::helper('confirmpayment')->__('Delete'),
        'url' => $this->getUrl('*/*/massDelete'),
        'confirm' => Mage::helper('confirmpayment')->__('Are you sure?')
    ));

    $statuses = Mage::getSingleton('confirmpayment/status')->getOptionArray();

    array_unshift($statuses, array('label' => '', 'value' => ''));
    $this->getMassactionBlock()->addItem('status', array(
        'label' => Mage::helper('confirmpayment')->__('Change status'),
        'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
        'additional' => array(
            'visibility' => array(
                'name' => 'status',
                'type' => 'select',
                'class' => 'required-entry',
                'label' => Mage::helper('confirmpayment')->__('Status'),
                'values' => $statuses
            ))
    ));
    return $this;
  }

  public function getRowUrl($row) {
    return $this->getUrl('*/*/view', array(
                'store' => $this->getRequest()->getParam('store'),
                'id' => $row->getId())
    );
  }

  public function getGridUrl() {
    return $this->getUrl('*/*/grid', array('_current' => true));
  }

}
