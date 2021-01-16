<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Block_Adminhtml_Images_Edit_Tab_Categories extends Mage_Adminhtml_Block_Widget_Grid {
  public function __construct() {
    parent::__construct();
    $this->setId('categoriesGrid');
    $this->setDefaultSort('category_id');
    $this->setUseAjax(TRUE);
    $categoryIds = $this->_getSelectedCategories();
  }

  protected function _addColumnFilterToCollection($column) {
    if ($column->getId() == 'in_categories') {

      if (empty($categoryIds)) {
        $categoryIds = 0;
      }
      if ($column->getFilter()->getValue()) {
        $this->getCollection()->addFieldToFilter('category_id', array('in' => $categoryIds));
      } else {
        if ($categoryIds) {
          $this->getCollection()->addFieldToFilter('category_id', array('nin' => $categoryIds));
        }
      }
    } else {
      parent::_addColumnFilterToCollection($column);
    }
    return $this;
  }

  protected function _prepareCollection() {
    $collection = Mage::getResourceModel('bannerads/categories_collection');
    $collection->addFieldToFilter('status', 1);
    $this->setCollection($collection);
    return parent::_prepareCollection();
  }

  protected function _prepareColumns() {
    $this->addColumn('in_categories', array('header_css_class' => 'a-center', 'type' => 'checkbox', 'name' => 'in_categories', 'align' => 'center', 'index' => 'category_id', 'values' => $this->_getSelectedCategories(),));

    $this->addColumn('category_id', array('header' => Mage::helper('bannerads')->__('ID'), 'width' => '50px', 'index' => 'category_id', 'type' => 'number',));

    $this->addColumn('category_title', array('header' => Mage::helper('bannerads')->__('Category Title'), 'index' => 'category_title'));
    return parent::_prepareColumns();
  }

  public function getGridUrl() {
    return $this->getData('grid_url') ? $this->getData('grid_url') : $this->getUrl('*/*/categorylistGrid', array('_current' => TRUE));
  }

  protected function _getSelectedCategories() {
    $categories = $this->getCategories();
    if (!is_array($categories)) {
      $categories = $this->getSelectedCategories();
    }
    return $categories;
  }

  public function getSelectedCategories() {
    $categories = array();
    $categories = $this->getBanner()->getSelectedCategoryIds();
    return $categories;
  }

  public function getBanner() {
    return Mage::getModel('bannerads/images')->load($this->getRequest()->getParam('id'));
  }
}