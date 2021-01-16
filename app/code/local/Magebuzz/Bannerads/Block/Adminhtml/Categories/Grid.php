<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Block_Adminhtml_Categories_Grid extends Mage_Adminhtml_Block_Widget_Grid {
  public function __construct() {
    parent::__construct();
    $this->setId('categoriesGrid');
    $this->setDefaultSort('category_id');
    $this->setDefaultDir('ASC');
    $this->setSaveParametersInSession(TRUE);
  }

  protected function _prepareCollection() {
    $collection = Mage::getModel('bannerads/categories')->getCollection();
    $this->setCollection($collection);
    return parent::_prepareCollection();
  }

  protected function _prepareColumns() {
    $this->addColumn('category_id', array('header' => Mage::helper('bannerads')->__('ID'), 'align' => 'right', 'width' => '50px', 'index' => 'category_id',));

    $this->addColumn('category_title', array('header' => Mage::helper('bannerads')->__('Title'), 'align' => 'left', 'index' => 'category_title',));

    $this->addColumn('category_description', array('header' => Mage::helper('bannerads')->__('Description'), 'index' => 'category_description',));

    $this->addColumn('status', array('header' => Mage::helper('bannerads')->__('Status'), 'align' => 'left', 'width' => '80px', 'index' => 'status', 'type' => 'options', 'options' => array(1 => 'Enabled', 2 => 'Disabled',),));

    $this->addColumn('action', array('header' => Mage::helper('bannerads')->__('Action'), 'width' => '100', 'type' => 'action', 'getter' => 'getId', 'actions' => array(array('caption' => Mage::helper('bannerads')->__('Edit'), 'url' => array('base' => '*/*/edit'), 'field' => 'id')), 'filter' => FALSE, 'sortable' => FALSE, 'index' => 'stores', 'is_system' => TRUE,));
    return parent::_prepareColumns();
  }

  protected function _prepareMassaction() {
    $this->setMassactionIdField('category_id');
    $this->getMassactionBlock()->setFormFieldName('bannerads');

    $this->getMassactionBlock()->addItem('delete', array('label' => Mage::helper('bannerads')->__('Delete'), 'url' => $this->getUrl('*/*/massDelete'), 'confirm' => Mage::helper('bannerads')->__('Are you sure?')));

    $statuses = Mage::getSingleton('bannerads/status')->getOptionArray();

    array_unshift($statuses, array('label' => '', 'value' => ''));
    $this->getMassactionBlock()->addItem('status', array('label' => Mage::helper('bannerads')->__('Change status'), 'url' => $this->getUrl('*/*/massStatus', array('_current' => TRUE)), 'additional' => array('visibility' => array('name' => 'status', 'type' => 'select', 'class' => 'required-entry', 'label' => Mage::helper('bannerads')->__('Status'), 'values' => $statuses))));
    return $this;
  }

  public function getRowUrl($row) {
    return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
}