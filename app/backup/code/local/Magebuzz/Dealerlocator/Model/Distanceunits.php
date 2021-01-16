<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/

class Magebuzz_Dealerlocator_Model_DistanceUnits {
  public function toOptionArray() {
    return array(array('value' => 0, 'label' => Mage::helper('adminhtml')->__('Mile')), array('value' => 1, 'label' => Mage::helper('adminhtml')->__('Kilometer')),);
  }

  public function toArray() {
    return array(0 => Mage::helper('adminhtml')->__('Mile'), 1 => Mage::helper('adminhtml')->__('Kilometer'),);
  }

}
