<?php

/**
 * Class Magebuzz_Membergroup_Model_Source_Config_CustomerGroup
 */
class Magebuzz_Membergroup_Model_Source_Config_CustomerGroup
{
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toOptionArray()
    {
        return Mage::helper('customer')->getGroups()->toOptionArray();
    }
}