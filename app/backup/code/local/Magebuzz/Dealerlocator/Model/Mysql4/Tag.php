<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/

class Magebuzz_Dealerlocator_Model_Mysql4_Tag extends Mage_Core_Model_Mysql4_Abstract {
  public function _construct() {
    $this->_init('dealerlocator/dealerlocator_tag', 'dealer_tag_id');
  }
}