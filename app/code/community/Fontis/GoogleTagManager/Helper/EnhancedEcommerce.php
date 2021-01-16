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

class Fontis_GoogleTagManager_Helper_EnhancedEcommerce extends Mage_Core_Helper_Abstract
{
    const ENHANCED_ECOMMERCE_MAX_CATEGORIES = 5;

    /**
     * Returns true if product details variant data is to be included in the data layer.
     *
     * @return bool
     */
    public function includeVariantData()
    {
        return Mage::getStoreConfigFlag('fontis_gtm/enhanced_ecommerce_product_details/add_variant');
    }

    /**
     * Returns the product attribute used to retrieve variant product value.
     *
     * @return bool
     */
    public function getVariantSourceAttribute()
    {
        return Mage::getStoreConfig('fontis_gtm/enhanced_ecommerce_product_details/variant_attribute');
    }

    /**
     * Builds the product data needed for enhanced ecommerce.
     *
     * See https://developers.google.com/analytics/devguides/collection/analyticsjs/enhanced-ecommerce#product-data
     * for a description of the fields returned from this function.
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function buildProductData(Mage_Catalog_Model_Product $product)
    {
        $brandName = '';

        if ($product->hasData('manufacturer')) {
            $brandName = $product->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($product);
        }

        /** @var $tokensHelper Fontis_Tokens_Helper_Provider_Other */
        $tokensHelper = Mage::helper('tokens/provider_other');

        $productData = array(
            'name'     => $product->getName(),
            'id'       => $product->getSku(),
            'price'    => $this->formatPrice($tokensHelper->getProductPrice($product)),
            'category' => implode('/', $this->getProductCategoryHierarchy($product)),
            'brand'    => $brandName
        );

        $variantAttributeCode = $this->getVariantSourceAttribute();

        if ($this->includeVariantData() && $product->hasData($variantAttributeCode)) {
            $productData['variant'] = $product->getResource()
                ->getAttribute($variantAttributeCode)->getFrontend()->getValue($product);
        }

        return $productData;
    }

    /**
     * Returns a hierarchical category list for the specified product.
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getProductCategoryHierarchy(Mage_Catalog_Model_Product $product)
    {
        $categoryPathIds = $this->_getProductCategoryIdPath($product);

        if (empty($categoryPathIds)) {
            return array();
        }

        $rootCategoryKey = array_search(Mage_Catalog_Model_Category::TREE_ROOT_ID, $categoryPathIds);

        if ($rootCategoryKey !== false) {
            unset($categoryPathIds[$rootCategoryKey]);
        }

        // If there are more than the maximum number of allowed categories,
        // simply take the first set.
        $categoryPathIds = array_slice($categoryPathIds, 0, self::ENHANCED_ECOMMERCE_MAX_CATEGORIES);

        $categoryCollection = $this->_prepareCategoryCollection($categoryPathIds);

        $categories = array();

        foreach ($categoryCollection as $category) {
            $categories[] = $category->getName();
        }

        return $categories;
    }

    /**
     * Returns the path to the  canonical category for a product (or the first
     * category if the canonical extension isn't present), expressed as an array
     * of category ids.
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function _getProductCategoryIdPath(Mage_Catalog_Model_Product $product)
    {
        $categoryPathIds = array();

        if ($this->isModuleEnabled('Fontis_CanonicalCategory')) {
            /** @var $canonicalCategoryHelper Fontis_CanonicalCategory_Helper_Data */
            $canonicalCategoryHelper = Mage::helper('fontis_canonicalcategory');
            $categoryPathIds = $canonicalCategoryHelper->getCanonicalCategoryIdPath($product);
        } else {
            $productCategoryIds = $product->getCategoryIds();

            if (count($productCategoryIds) >= 1) {
                // Retrieve the path of the first category this product is in.
                /** @var $category Mage_Catalog_Model_Category */
                $category = Mage::getModel('catalog/category')->load($productCategoryIds[0]);
                $categoryPathIds = array_map('intval', $category->getPathIds());
            }
        }

        return $categoryPathIds;
    }

    /**
     * Returns a category collection, containing the entries specified in
     * $categoryPathIds.
     *
     * @param array $categoryPathIds
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    protected function _prepareCategoryCollection(array $categoryPathIds)
    {
        /** @var $categoryCollection Mage_Catalog_Model_Resource_Category_Collection */
        $categoryCollection = Mage::getModel('catalog/category')->getCollection();
        $categoryCollection->addAttributeToSelect('name');
        $categoryCollection->addIdFilter($categoryPathIds);
        $categoryCollection->getSelect()->order(new Zend_Db_Expr('FIELD(e.entity_id, ' . implode(',', $categoryPathIds) . ')'));

        return $categoryCollection;
    }

    /**
     * Formats prices used for enhanced ecommerce.
     *
     * Note that the price is returned as a string. In the enhanced ecommerce
     * documentation, the type of the price attribute is listed as "Currency",
     * which appears to mean string. The examples in the documentation also use
     * a string. This is in contrast to the standard ecommerce tracking, which
     * uses numerical values directly.
     *
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return number_format($price, 2, '.', '');
    }
}
