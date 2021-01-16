<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/

class Magebuzz_Dealerlocator_Model_System_Config_Source_Pagetype {
  public function toOptionArray() {
    return array(array('value' => 2, 'label' => Mage::helper('dealerlocator')->__('2 columns with map on the right')), array('value' => 1, 'label' => Mage::helper('dealerlocator')->__('1 column')),);
  }
}