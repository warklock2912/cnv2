<?php

class Magebuzz_Confirmpayment_Model_Status extends Varien_Object {

  const STATUS_NEW = 1;
  const STATUS_COMPLETE = 2;

  static public function getOptionArray() {
    return array(
        self::STATUS_NEW => Mage::helper('confirmpayment')->__('New'),
        self::STATUS_COMPLETE => Mage::helper('confirmpayment')->__('Complete')
    );
  }

  static public function getOptionHash() {
    $options = array();
    foreach (self::getOptionArray() as $value => $label) {
      $options[] = array(
          'value' => $value,
          'label' => $label
      );
    }

    return $options;
  }

}
