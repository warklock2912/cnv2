<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Model_Mysql4_Banneradsstore extends Mage_Core_Model_Mysql4_Abstract {
  public function _construct() {
    // Note that the bannerads_id refers to the key field in your database table.
    $this->_init('bannerads/banneradsstore', 'block_id');
  }
}