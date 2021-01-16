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

class Fontis_Tokens_Helper_Provider_Customer extends Fontis_Tokens_Helper_Provider_Abstract
{
    /**
     * Get Customer data
     *
     * Get's the data from the session
     *
     * @param string $key
     * @return string|null
     */
    public function getTokenValue($key)
    {
        $customer = Mage::getModel('customer/session')->getCustomer();

        if (!$customer) {
            // Silently fail here rather than spam a log message or exception because in normal usage this will be very common
            return "";
        }

        // Customer Attributes
        if ($customer->hasData($key)) {
            if ($key == "default_billing" || $key == "default_shipping") {
                $address = Mage::getModel('customer/address')->load($customer->getData($key));
                return $address->format('string');
            } else {
                return $customer->getData($key);
            }
        }

        return null;
    }
}
