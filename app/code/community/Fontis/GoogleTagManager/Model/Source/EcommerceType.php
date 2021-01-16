<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Fontis Software License that is available in
 * the FONTIS-LICENSE.txt file included with this extension. This file is located
 * by default in the root directory of your Magento installation. If you are unable
 * to obtain the license from the file, please contact us via our website and you
 * will be sent a copy.
 *
 * @category   Fontis
 * @copyright  Copyright (c) 2015 Fontis Pty. Ltd. (http://www.fontis.com.au)
 * @license    Fontis Software License
 */

class Fontis_GoogleTagManager_Model_Source_EcommerceType
{
    const ECOMMERCE_TYPE_STANDARD = 0;
    const ECOMMERCE_TYPE_ENHANCED = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('adminhtml');

        return array(
            array(
                'value' => self::ECOMMERCE_TYPE_STANDARD,
                'label' => $helper->__('Standard')
            ),
            array(
                'value' => self::ECOMMERCE_TYPE_ENHANCED,
                'label' => $helper->__('Enhanced')
            ),
        );
    }
}
