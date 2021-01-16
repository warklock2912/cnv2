<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Block_Adminhtml_Images_Edit_Tab_Block extends Mage_Adminhtml_Block_Widget_Grid {
  public function __construct() {
    parent::__construct();
    $this->setId('imageblockGrid');
    $this->setUseAjax(TRUE);
    $this->setDefaultFilter(array('selected_blocks' => 1));
  }

  protected function _addColumnFilterToCollection($column) {
    if ($column->getId() == 'selected_blocks') {
      $blockIds = $this->_getSelectedBlocks();
      if (empty($blockIds)) {
        $blockIds = 0;
      }
      if ($column->getFilter()->getValue()) {
        $this->getCollection()->addFieldToFilter('block_id', array('in' => $blockIds));
      } else {
        if ($blockIds) {
          $this->getCollection()->addFieldToFilter('block_id', array('nin' => $blockIds));
        }
      }
    } else {
      parent::_addColumnFilterToCollection($column);
    }
    return $this;
  }

  protected function _prepareCollection() {
    $collection = Mage::getModel('bannerads/bannerads')->getCollection();
    $collection->addFieldToFilter('status', 1);
    $this->setCollection($collection);
    return parent::_prepareCollection();
  }

  protected function _prepareColumns() {
    $this->addColumn('selected_blocks', array(
      'header_css_class' => 'a-center',
      'type' => 'checkbox',
      'field_name' => 'selected_blocks',
      'align' => 'center',
      'index' => 'block_id',
      'values' => $this->_getSelectedBlocks(),
    ));

//    $this->addColumn('block_id', array(
//      'header' => Mage::helper('bannerads')->__('Block ID'),
//      'width' => '50px',
//      'index' => 'block_id',
//      'type' => 'number',
//    ));

    $this->addColumn('block_title', array(
      'header' => Mage::helper('bannerads')->__('Block Title'),
      'index' => 'block_title'
    ));

    return parent::_prepareColumns();
  }

  public function getGridUrl() {
    return $this->getData('grid_url') ? $this->getData('grid_url') : $this->getUrl('*/*/block_tab', array('_current' => TRUE));
  }
  
  public function getRowUrl($row) {
    return "#!";
  }

  protected function _getSelectedBlocks() {
    $blocks = $this->getBlocks();

    if (!is_array($blocks)) {
      $blocks = $this->getSelectedBlocks();
    }
    return $blocks;
  }

  public function getSelectedBlocks() {
    $block_ids = $this->getBanner()->getSelectedBlockIds();
    if ($this->getRequest()->getParam('from_block_id')) $block_ids[] = $this->getRequest()->getParam('from_block_id');
    return $block_ids;
  }

  public function getBanner() {
    return Mage::getModel('bannerads/images')->load($this->getRequest()->getParam('id'));
  }
}