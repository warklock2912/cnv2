<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Block_Adminhtml_Bannerads_Edit_Tab_Imagebanner extends Mage_Adminhtml_Block_Widget_Grid {
  public function __construct() {
    parent::__construct();
    $this->setId('ImagebannerGrid');
    $this->setDefaultSort('banner_id');
    $this->setUseAjax(TRUE);    
		if ($this->getBanner()->getId()) {
			$this->setDefaultFilter(array('in_banner' => 1));
		}
  }

  protected function _addColumnFilterToCollection($column) {
    if ($column->getId() == 'in_banner') {
			$imagebanner = $this->_getSelectedBannerImages();
      if (empty($imagebanner)) {
        $imagebanner = 0;
      }
      if ($column->getFilter()->getValue()) {
        $this->getCollection()->addFieldToFilter('banner_id', array('in' => $imagebanner));
      } else {
        if ($imagebanner) {
          $this->getCollection()->addFieldToFilter('banner_id', array('nin' => $imagebanner));
        }
      }
    } else {
      parent::_addColumnFilterToCollection($column);
    }
    return $this;
  }

  protected function _prepareCollection() {
    $collection = Mage::getResourceModel('bannerads/images_collection');
    $this->setCollection($collection);
    return parent::_prepareCollection();
  }

  protected function _prepareColumns() {

    $this->addColumn('in_banner', array('header_css_class' => 'a-center', 'type' => 'checkbox', 'name' => 'in_banner', 'align' => 'center', 'index' => 'banner_id', 'values' => $this->_getSelectedBannerImages(),));

    $this->addColumn('banner_id', array('header' => Mage::helper('bannerads')->__('ID'), 'align' => 'right', 'width' => '50px', 'index' => 'banner_id',));

    $this->addColumn('banner_title', array('header' => Mage::helper('bannerads')->__('Title'), 'align' => 'left', 'index' => 'banner_title',));

    $this->addColumn('banner_image', array('header' => Mage::helper('bannerads')->__('Image'), 'index' => 'banner_image', 'align' => 'center', 'renderer' => 'Magebuzz_Bannerads_Block_Adminhtml_Images_Renderer_Images'));

    $this->addColumn('banner_url', array('header' => Mage::helper('bannerads')->__('Banner Url'), 'index' => 'banner_url',));

    $this->addColumn('banner_description', array('header' => Mage::helper('bannerads')->__('Banner Description'), 'index' => 'banner_description',));

    $this->addColumn('sort_order', array('header' => Mage::helper('bannerads')->__('Sort Order'), 'index' => 'sort_order',));

    return parent::_prepareColumns();
  }

  public function getGridUrl() {
    return $this->getData('grid_url') ? $this->getData('grid_url') : $this->getUrl('*/*/imagelistGrid', array('_current' => TRUE));
  }

  protected function _getSelectedBannerImages() {
    $images = $this->getImages();
    if (!is_array($images)) {
      $images = $this->getSelectedBannerImages();
    }
    return $images;
  }

  public function getSelectedBannerImages() {
    $images = array();
    $images = $this->getBanner()->getSelectedImageIds();
    return $images;
  }

  public function getBanner() {
    return Mage::getModel('bannerads/bannerads')->load($this->getRequest()->getParam('id'));
  }
}
