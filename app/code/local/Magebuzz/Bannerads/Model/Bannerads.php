<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Model_Bannerads extends Mage_Core_Model_Abstract {
  public function _construct() {
    parent::_construct();
    $this->_init('bannerads/bannerads');
  }

  public function getSelectedImageIds() {
    $imageIds = array();
    $collection = Mage::getModel('bannerads/bannerblock')->getCollection()->addFieldToFilter('block_id', $this->getId());
    if (count($collection)) {
      foreach ($collection as $item) {
        $imageIds[] = $item->getBannerId();
      }
    }
    return $imageIds;
  }
}