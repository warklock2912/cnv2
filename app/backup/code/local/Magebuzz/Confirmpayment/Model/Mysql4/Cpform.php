<?php

/*
 * Copyright (c) 2015 www.magebuzz.com
 */

class Magebuzz_Confirmpayment_Model_Mysql4_Cpform extends Mage_Core_Model_Mysql4_Abstract {

  public function _construct() {
    $this->_init('confirmpayment/cpform', 'form_id');
  }

}
