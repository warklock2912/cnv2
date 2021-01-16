<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/

class Magebuzz_Dealerlocator_Model_Mysql4_Dealerlocator_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
  public function _construct() {
    parent::_construct();
    $this->_init('dealerlocator/dealerlocator');
  }
}