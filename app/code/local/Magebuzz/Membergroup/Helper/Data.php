<?php

/**
 * Class Magebuzz_Membergroup_Helper_Data
 */
class Magebuzz_Membergroup_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @return mixed
     */
    public function getMinimumNumber()
  {
    return Mage::getStoreConfig('membergroup/general/minimum_number');
  }

}