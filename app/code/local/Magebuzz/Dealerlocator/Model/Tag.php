<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/

class Magebuzz_Dealerlocator_Model_Tag extends Mage_Core_Model_Abstract {
  public function _construct() {
    parent::_construct();
    $this->_init('dealerlocator/tag');
  }
}