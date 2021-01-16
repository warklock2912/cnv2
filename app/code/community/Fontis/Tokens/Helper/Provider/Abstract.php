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

abstract class Fontis_Tokens_Helper_Provider_Abstract extends Mage_Core_Helper_Abstract
{
    /**
     * Callback to get token value from a key
     *
     * Returns a string result, or false if the token cannot be found.
     *
     * @param $key string
     * @return string|null
     */
    abstract public function getTokenValue($key);

    /**
     * Get the price of a product.
     *
     * This method should be safe to call on all core Magento product types
     *
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getProductPrice($product)
    {
        $price = $product->getFinalPrice();

        // price being 0 happens for bundle products with dynamic prices, so if this happens, we'll
        // try using a bundle product method to get the lowest possible price:
        if ($price == 0) {
            $priceModel = $product->getPriceModel();
            if ($priceModel instanceof Mage_Bundle_Model_Product_Price) {
                // getPrices is deprecated after 1.5.1.0, so call getTotalPrices if it exists
                if (method_exists($priceModel, 'getTotalPrices')) {
                    $price = $priceModel->getTotalPrices($product, 'min');
                } else {
                    $prices = $priceModel->getPrices($product);
                    $price = $prices[0];
                }
            } else {
                // If it's not a bundle, there's nothing much else we can do. We'll just return 0 which is
                // as close as we can get to a valid price. If we return false here, we'll be including the
                // [price] token in the output, which we want to avoid.
            }
        }

        return $price;
    }

    /**
     * Format a price for embedding in a tokenised string
     *
     * Using number_format as this gives us the freedom to add in a $ or not as required. If we use the core magento
     * locale formatting, we are forced to use the $
     *
     * @param float|string $price
     * @return string
     */
    public function formatPrice($price)
    {
        return number_format($price, 2);
    }
}
