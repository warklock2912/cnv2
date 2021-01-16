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

class Fontis_Tokens_Helper_Provider_Catalog_Product extends Fontis_Tokens_Helper_Provider_Abstract
{
    /**
     * Gets the token value for a product page
     *
     * Has manual checking for price, qty and available attributes, otherwise falls back to the product attributes.
     *
     * @param string $key
     * @return string|null
     * @throws Mage_Core_Exception
     */
    public function getTokenValue($key)
    {
        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::registry('current_product');

        if (!$product) {
            // Silently fail here rather than spam a log message or exception because in normal usage this will be very common
            return "";
        }

        switch ($key) {
            case 'price':
                return $this->formatPrice($this->getProductPrice($product));

            case 'qty':
                return $product->getStockItem()->getQty();

            case 'available':
                return $product->isSalable();

            case 'canonical_category':
                return $this->getCanonicalCategoryName($product);
        }

        // if the token is a product attribute, go ahead and return a textual representation of the attribute value
        if ($product->hasData($key)) {
            return $product->getResource()->getAttribute($key)->getFrontend()->getValue($product);
        }

        return null;
    }

    /**
     * Determines whether the Fontis Canonical Category extension is installed, and if so, returns the canonical category
     * name.
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string|null
     */
    protected function getCanonicalCategoryName(Mage_Catalog_Model_Product $product)
    {
        if ($this->isModuleEnabled(Fontis_Tokens_Helper_Data::MODULE_FONTIS_CANONICALCATEGORY)) {
            /** @var Mage_Catalog_Model_Category $canonicalCategory */
            $canonicalCategory = Mage::helper('fontis_canonicalcategory')->getCanonicalCategory($product);
            if ($canonicalCategory !== null) {
                return $canonicalCategory->getName();
            }
        }
        return null;
    }
}
