<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteAutocomplete
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuiteAutocomplete_Block_Autocomplete extends Mage_Catalog_Block_Product {

    protected $_size = array();

    protected function _construct() {
        parent::_construct();
        $this->setData('area', 'frontend');
        $this->setTemplate('mageworx/searchsuiteautocomplete/popup.phtml');
        $this->_size = Mage::helper('mageworx_searchsuiteautocomplete')->getProductImageSize();
    }

    protected function getProductsGroupedByCategories() {
        $groupedProducts = $this->explodeProductsByCategory($this->getProducts());
        usort($groupedProducts, array('MageWorx_SearchSuiteAutocomplete_Block_Autocomplete', 'cmpCategories'));
        foreach ($groupedProducts as $key => $group) {
            $groupedProducts[$key]['products'] = $group['products'];
        }
        return $groupedProducts;
    }

    /**
     *  Callback function. Compare Categories
     * 
     * @param type $a
     * @param type $b
     * @return type int
     */
    public static function cmpCategories($a, $b) {
        $countproducts1 = count($a['products']);
        $countproducts2 = count($b['products']);
        if ($countproducts1 > $countproducts2)
            return -1;
        elseif ($countproducts1 == $countproducts2)
            return 0;
        else
            return 1;
    }

    /**
     *  Explode Products By Category (Priority - low level category)
     * 
     * @param type $products
     * @return array Exploded Products By Category 
     */
    protected function explodeProductsByCategory($products) {
        $categoriesWithGroupedProducts = array();
        foreach ($products as $product) {
            $categoryIds = $product->getCategoryIds();
            $categories = array();
            foreach ($categoryIds as $categoryId) {
                $cat = Mage::getModel('catalog/category')->load($categoryId);
                if ($cat) {
                    array_push($categories, $cat);
                }
            }
            $categories = $this->filterCategoriesByLevel($categories);
            foreach ($categories as $category) {
                $id = $category->getId();
                if (!isset($categoriesWithGroupedProducts[$id])) {
                    $categoriesWithGroupedProducts[$id] = array('category' => $category, 'products' => array($product));
                } else {
                    $categoriesWithGroupedProducts[$id]['products'][] = $product;
                }
            }
        }
        return $categoriesWithGroupedProducts;
    }

    /**
     *  Filter Categories By Low Level
     * 
     * @param type $categories
     * @return array Filtered Categories
     */
    protected function filterCategoriesByLevel($categories) {
        $low = 0;
        $filtered = array();
        foreach ($categories as $category) {
            $level = $category->getLevel();
            if ($level > $low)
                $low = $level;
        }
        foreach ($categories as $category) {
            $level = $category->getLevel();
            if ($level == $low) {
                array_push($filtered, $category);
            }
        }
        return $filtered;
    }

    protected function _sortProducts($products) {

        return $products;
    }

    /**
     * Retrieve attribute instance by name, id or config node
     *
     * If attribute is not found false is returned
     *
     * @param string|integer|Mage_Core_Model_Config_Element $attribute
     * @return Mage_Eav_Model_Entity_Attribute_Abstract || false
     */
    public function getProductAttribute($attribute) { // for 1.13.0.0
        return $this->getProduct()->getResource()->getAttribute($attribute);
    }

    public function getPriceHtml($product) {

        if ($product->getTypeId() == 'bundle') {
            return $this->getLayout()
                            ->createBlock('bundle/catalog_product_price')
                            ->setTemplate('bundle/catalog/product/price.phtml')
                            ->setProduct($product)
                            ->toHtml();
        } else {
            return parent::getPriceHtml($product);
        }
    }

    public function getProductImageUrl($product) {
        return Mage::helper('catalog/image')->init($product, 'image')->resize($this->_size[0], $this->_size[1]);
    }

}
