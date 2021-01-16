<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */
class Amasty_Customerattr_Model_System_Config_Source_Attributes
{
    public function toOptionArray()
    {
        $hash = Mage::helper('amcustomerattr')->getAttributesHash();
        $options = array();
        $options[] = array('value' => '',
                           'label' => Mage::helper('amcustomerattr')->__(
                               '- Magento Default (E-mail) -'
                           ));
        foreach ($hash as $key => $option) {
            $options[] = array('value' => $key, 'label' => $option);
        }
        return $options;
    }
}