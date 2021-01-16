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

class Fontis_Tokens_Helper_Provider_Checkout_Cart extends Fontis_Tokens_Helper_Provider_Abstract
{
    /**
     * Get values for cart page data
     *
     * Custom supported data: summary_qty, items_count, items_qty
     *
     * @param string $key
     * @return string|null
     */
    public function getTokenValue($key)
    {
        /* @var $cart Mage_Checkout_Model_Cart */
        $cart = Mage::getSingleton('checkout/cart');

        // check common cart properties
        switch ($key) {
            case 'summary_qty':
                return $cart->getSummaryQty();

            case 'items_count':
                return $cart->getItemsCount();

            case 'items_qty':
                return $cart->getItemsQty();
        }

        $quote = $cart->getQuote();

        // try quote attributes - such as totals
        if ($quote->hasData($key)) {
            return $quote->getData($key);
        }

        return null;
    }
}
