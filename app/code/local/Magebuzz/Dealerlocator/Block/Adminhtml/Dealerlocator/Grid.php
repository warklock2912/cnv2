<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/

class Magebuzz_Dealerlocator_Block_Adminhtml_Dealerlocator_Grid extends Mage_Adminhtml_Block_Widget_Grid {
  public function __construct() {
    parent::__construct();
    $this->setId('dealerlocatorGrid');
    $this->setDefaultSort('dealerlocator_id');
    $this->setDefaultDir('ASC');
    $this->setSaveParametersInSession(TRUE);
  }

  protected function _prepareCollection() {
    $collection = Mage::getModel('dealerlocator/dealerlocator')->getCollection();
    $this->setCollection($collection);
    return parent::_prepareCollection();
  }

  protected function _prepareColumns() {
    $this->addColumn('dealerlocator_id', array('header' => Mage::helper('dealerlocator')->__('ID'), 'align' => 'right', 'width' => '50px', 'index' => 'dealerlocator_id',));

    $this->addColumn('title', array('header' => Mage::helper('dealerlocator')->__('Title'), 'align' => 'left', 'index' => 'title',));

    $this->addColumn('email', array('header' => Mage::helper('dealerlocator')->__('Email'), 'align' => 'left', 'index' => 'email',));

    $this->addColumn('website', array('header' => Mage::helper('dealerlocator')->__('Website'), 'align' => 'left', 'index' => 'website',));

    $this->addColumn('phone', array('header' => Mage::helper('dealerlocator')->__('Phone'), 'align' => 'left', 'index' => 'phone',));

    $this->addColumn('postal_code', array('header' => Mage::helper('dealerlocator')->__('Postal Code'), 'align' => 'left', 'index' => 'postal_code',));

    $this->addColumn('address', array('header' => Mage::helper('dealerlocator')->__('Address'), 'align' => 'left', 'index' => 'address',));

    $this->addColumn('longitude', array('header' => Mage::helper('dealerlocator')->__('Longitude'), 'align' => 'left', 'index' => 'longitude',));

    $this->addColumn('latitude', array('header' => Mage::helper('dealerlocator')->__('Latitude'), 'align' => 'left', 'index' => 'latitude',));

    $this->addColumn('status', array('header' => Mage::helper('dealerlocator')->__('Status'), 'align' => 'left', 'index' => 'status', 'type' => 'options', 'options' => array(1 => 'Enabled', 2 => 'Disabled',),));

    $this->addColumn('note', array('header' => Mage::helper('dealerlocator')->__('Note'), 'align' => 'left', 'index' => 'note',));

    $this->addColumn('action', array('header' => Mage::helper('dealerlocator')->__('Action'), 'width' => '100', 'type' => 'action', 'getter' => 'getId', 'actions' => array(array('caption' => Mage::helper('dealerlocator')->__('Edit'), 'url' => array('base' => '*/*/edit'), 'field' => 'id')), 'filter' => FALSE, 'sortable' => FALSE, 'index' => 'stores', 'is_system' => TRUE,));

    $this->addExportType('*/*/exportCsv', Mage::helper('dealerlocator')->__('CSV'));
    $this->addExportType('*/*/exportXml', Mage::helper('dealerlocator')->__('XML'));

    return parent::_prepareColumns();
  }

  protected function _prepareMassaction() {
    $this->setMassactionIdField('dealerlocator_id');
    $this->getMassactionBlock()->setFormFieldName('dealerlocator');

    $this->getMassactionBlock()->addItem('delete', array('label' => Mage::helper('adminhtml')->__('Delete'), 'url' => $this->getUrl('*/*/massDelete'), 'confirm' => Mage::helper('adminhtml')->__('Are you sure?')));

    $statuses = Mage::getSingleton('dealerlocator/status')->getOptionArray();

    array_unshift($statuses, array('label' => '', 'value' => ''));
    $this->getMassactionBlock()->addItem('status', array('label' => Mage::helper('adminhtml')->__('Change status'), 'url' => $this->getUrl('*/*/massStatus', array('_current' => TRUE)), 'additional' => array('visibility' => array('name' => 'status', 'type' => 'select', 'class' => 'required-entry', 'label' => Mage::helper('dealerlocator')->__('Status'), 'values' => $statuses))));
    return $this;
  }

  public function getRowUrl($row) {
    return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}