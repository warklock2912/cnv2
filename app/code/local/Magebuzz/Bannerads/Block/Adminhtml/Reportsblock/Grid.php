<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Block_Adminhtml_Reportsblock_Grid extends Mage_Adminhtml_Block_Widget_Grid {
  public function __construct() {
    parent::__construct();
    $this->setId('banneradsGridbanner');
    $this->setUseAjax(TRUE);
    $this->setDefaultSort('report_id');
    $this->setDefaultDir('ASC');
    $this->setSaveParametersInSession(TRUE);
  }

  protected function _prepareCollection() {
    $collection = Mage::getModel('bannerads/reports')->getCollection();
    $collection->getSelect()->join(array('t2' => Mage::getSingleton('core/resource')->getTableName('bannerads/bannerads')), 'main_table.block_id = t2.block_id')->columns('sum(clicks) as count_click')->columns('sum(impression) as count_view')->group('main_table.block_id');
    $this->setCollection($collection);
    return parent::_prepareCollection();
  }

  protected function _prepareColumns() {

    $this->addColumn('block_title', array('header' => Mage::helper('bannerads')->__('Block Title'), 'align' => 'left', 'index' => 'block_title',));

    $this->addColumn('block_position', array('header' => Mage::helper('bannerads')->__('Block Position'), 'align' => 'left', 'index' => 'block_position'));

    $this->addColumn('count_view', array('header' => Mage::helper('bannerads')->__('Count Show'), 'align' => 'left', 'index' => 'count_view',));

    $this->addColumn('count_click', array('header' => Mage::helper('bannerads')->__('Count Click'), 'align' => 'left', 'index' => 'count_click',));
    $this->addExportType('*/*/exportCsv', Mage::helper('bannerads')->__('CSV'));
    $this->addExportType('*/*/exportXml', Mage::helper('bannerads')->__('XML'));

    return parent::_prepareColumns();
  }

  protected function _prepareMassaction() {
    $this->setMassactionIdField('report_ids');
    $this->getMassactionBlock()->setFormFieldName('report_ids');

    $this->getMassactionBlock()->addItem('delete', array('label' => Mage::helper('bannerads')->__('Delete'), 'url' => $this->getUrl('*/*/massDelete'), 'confirm' => Mage::helper('bannerads')->__('Are you sure?')));

    return $this;
  }

  public function getGridUrl() {
    return $this->getUrl('*/*/grid', array('_current' => TRUE));
  }
}
