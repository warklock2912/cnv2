<?php

class Magebuzz_Membergroup_Helper_Data extends Mage_Core_Helper_Abstract
{
  public function getMinimumNumber()
  {
    return Mage::getStoreConfig('membergroup/general/minimum_number');
  }

}