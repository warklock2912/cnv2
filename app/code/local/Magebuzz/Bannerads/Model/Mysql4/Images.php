<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Model_Mysql4_Images extends Mage_Core_Model_Mysql4_Abstract {
  public function _construct() {
    $this->_init('bannerads/images', 'banner_id');
  }
}