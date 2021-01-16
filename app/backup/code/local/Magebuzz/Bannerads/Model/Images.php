<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Model_Images extends Mage_Core_Model_Abstract {
  public function _construct() {
    parent::_construct();
    $this->_init('bannerads/images');
  }

  public function getSelectedBlockIds() {
    $block_ids = array();
    $collection = Mage::getModel('bannerads/bannerblock')->getCollection()->addFieldToFilter('banner_id', $this->getId());
    if ($collection->count()) {
      foreach($collection->getItems() as $item) {
        $block_ids[] = $item->getBlockId();
      }
    }
    return $block_ids;
  }
}