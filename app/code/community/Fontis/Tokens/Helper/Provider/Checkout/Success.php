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

class Fontis_Tokens_Helper_Provider_Checkout_Success extends Fontis_Tokens_Helper_Provider_Abstract
{
    /**
     * Get token values for order success page
     *
     * Handles totals and status specially, else falls back to order object data
     *
     * @param string $key
     * @return string|float|null
     */
    public function getTokenValue($key)
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order')->load(Mage::getSingleton('checkout/session')->getLastOrderId());

        // handle order totals and status specially
        switch ($key) {
            case 'status':
                return $order->getStatusLabel();

            case 'total':
                return $order->getBaseGrandTotal();

            case 'shipping':
                return $order->getShippingAmount() + $order->getShippingTaxAmount();

            case 'tax':
                return $order->getTaxAmount();
        }

        // try falling back to order object data
        if ($order->hasData($key)) {
            return $order->getData($key);
        }

        return null;
    }
}
