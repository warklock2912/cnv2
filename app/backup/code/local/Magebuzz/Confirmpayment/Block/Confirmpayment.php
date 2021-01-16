<?php

class Magebuzz_Confirmpayment_Block_Confirmpayment extends Mage_Core_Block_Template {

  public function _prepareLayout() {
    return parent::_prepareLayout();
  }
  public function getBanks() {
    $bankStr = Mage::getStoreConfig('confirmpayment/info/bank');
    $bankArr = array();
    if ($bankStr) {
      $bankArr = explode(',',$bankStr);
    }
    return $bankArr;
  }
}
