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

class Fontis_Tokens_Helper_Provider_Catalog_Category extends Fontis_Tokens_Helper_Provider_Abstract
{
    /**
     * @var Mage_Catalog_Model_Product
     */
    protected $_cheapestProduct = null;

    /**
     * Find the value of a token on a category page
     *
     * Checks current category attributes, as well as custom options, like total number of products
     *
     * @throws Mage_Core_Exception
     * @param string $key
     * @return string|null
     */
    public function getTokenValue($key)
    {
        $category = Mage::registry('current_category');

        if (!$category) {
            // Silently fail here rather than spam a log message or exception because in normal usage this will be very common
            return "";
        }

        switch ($key) {
            case 'product_count':
                return $this->getLayer()->getProductCollection()->count();

            case 'minimum_price':
                return $this->formatPrice($this->getProductPrice($this->getCheapestProduct($category)));
        }

        // handle category attributes
        if ($category->hasData($key)) {
            return $category->getData($key);
        }

        return null;
    }

    /**
     * Find the cheapest product in the supplied category
     *
     * @param Mage_Catalog_Model_Category $category
     * @return Mage_Catalog_Model_Product
     */
    public function getCheapestProduct(Mage_Catalog_Model_Category $category)
    {
        if ($this->_cheapestProduct === null) {
            $collection = $this->getLayer()->getProductCollection();

            $this->_cheapestProduct = $collection->getFirstItem();
            $cheapestPrice = $this->getProductPrice($this->_cheapestProduct);

            foreach ($collection as $product) {
                $price = $this->getProductPrice($product);

                if ($price < $cheapestPrice && $price > 0) {
                    $this->_cheapestProduct = $product;
                    $cheapestPrice = $price;
                }
            }
        }

        return $this->_cheapestProduct;
    }

    /**
     * Get catalog layer model
     *
     * @return Mage_Catalog_Model_Layer
     */
    public function getLayer()
    {
        $layer = Mage::registry('current_layer');
        if ($layer) {
            return $layer;
        }
        return Mage::getSingleton('catalog/layer');
    }
}
