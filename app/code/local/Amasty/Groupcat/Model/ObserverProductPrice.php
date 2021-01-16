<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */
class Amasty_Groupcat_Model_ObserverProductPrice
{
    const FORBIDDEN_ACTION_404      = '1';
    const FORBIDDEN_ACTION_REDIRECT = '2';

    /*
     * check restrictions for "hide_price"
     */
    public function hideProductsPrice(Varien_Event_Observer $observer)
    {
        if (!Mage::getStoreConfig('amgroupcat/general/disable')) {
            return false;
        }
        /** @var $block Mage_Core_Block_Abstract */
        $block     = $observer->getBlock();
        $output = $observer->getTransport()->getHtml();
        if(!$output) {
            return $output;
        }

        if ($block instanceof Mage_Catalog_Block_Product_List)
        {
            $output = $this->_processCatalogProductList($output, $block);
        }

        else if (
            $block instanceof Mage_Catalog_Block_Product_View
            ||  $block instanceof Mage_Catalog_Block_Product_Price
        ) {
            $output = $this->_processCatalogProduct($output, $block);
        }

        else if (
            $block instanceof Infortis_UltraMegamenu_Block_Navigation
            || $block instanceof Mage_Page_Block_Html_Topmenu
            || $block instanceof Mage_CatalogSearch_Block_Layer
        ) {
            $output = $this->_processHideFromMenu($output, $block);
        }

        $observer->getTransport()->setHtml($output);
        return $this;
    }


    /*
     * check restrictions for Category Hiding from menu
     * fix specially for Infortis Themes & Layered Navigation
     */
    protected function _processHideFromMenu($output, $block) {
        $categoryIds = array();
        $storeId = Mage::app()->getStore()->getStoreId();
        $activeRules = $this->_getHelper()->getActiveRules(
            array('remove_category_links = 1')/* active rules which have "remove_category_links" flag */
        );

        if (!empty($activeRules)) {
            foreach ($activeRules as $rule) {
                $ids         = Mage::helper('amgroupcat')->getRestrictedCategories($rule);
                $categoryIds = array_merge($ids, $categoryIds);
            }

            $categoryIds = array_unique($categoryIds);
        }
        if (empty($activeRules) || empty($categoryIds) ) {
           return $output;
        }

        /** @var $processor Amasty_Groupcat_Model_Htmlprocessor_Interface */
        $processor = Mage::getModel('amgroupcat/htmlprocessor_factory')->createProcessor();
        $processor->load($output);

        foreach ($categoryIds as $id) {
            if ($id <= 0) {
                continue;
            }
            // get category URL
            $category      = Mage::getModel('catalog/category')->setStoreId($storeId)->load($id);
            $categoryUrl   = $category->getUrl();

            // remove all matches for links on restricted categories
            $processor->remove('a[@href="' . $categoryUrl . '"]');

            // remove links from Layered Navigation
            if ($block instanceof Mage_CatalogSearch_Block_Layer) {
                $currentUrl = Mage::helper('core/url')->getCurrentUrl();

                if (strpos($currentUrl, '?') !== false) {
                    $currentUrl = substr($currentUrl, 0, strpos($currentUrl, '?'));
                }
                $currentUrl .= '?cat=' . $id;
                $processor->remove('a[@href="' . $currentUrl . '"]');
            }

            $output = $processor->getHtml();
        }

        return $output;
    }

    protected function _processCatalogProduct($output, $block) {
        try {
            $activeRules = $this->_getHelper()->getActiveRulesForProductPrice($block->getProduct());
        } catch (Exception $e) {
            return $output;
        }

        if (empty($activeRules)) {
            return $output;
        }

        /*
         * apply rules for product
         */
        /** @var $processor Amasty_Groupcat_Model_Htmlprocessor_Interface */
        $processor = Mage::getModel('amgroupcat/htmlprocessor_factory')->createProcessor();
        $processor->load($output);
        
        foreach ($activeRules as $rule) {
            if ($rule['hide_price']) {
                /*process product view page*/
                if ($block instanceof Mage_Catalog_Block_Product_View ||
                    (Mage::app()->getFrontController()->getRequest()->getControllerName() == 'product'
                        && Mage::registry('current_product')
                        && Mage::registry('current_product')->getId() == $block->getProduct()->getId())
                ) {
                    $replace = ' ';
                    if ($rule['price_on_product_view']) {
                        $replace = Mage::helper('cms')->getPageTemplateProcessor()->filter(
                            Mage::getModel('cms/block')->load($rule['price_on_product_view'])
                                ->getContent()
                        );
                    }

                    $priceSelector = $this->_getHelper()->getCssSelector('product_view_price');

                    $removeBlockSelectors = array(
                        'product_view_qty', 'product_view_qtylabel', 'product_view_addtocart',
                        'product_view_tier_price', 'product_view_price_notice', 'product_view_price_bundle');
                    foreach ($removeBlockSelectors as $blockSelector) {
                        $processor->remove($this->_getHelper()->getCssSelector($blockSelector));
                    }
                } else {
                    /*process product price block*/
                    $replace = $this->_getButtonHideHtml($block->getProduct()->getId());
                    if ($rule['price_on_product_list']) {
                        $replace .= Mage::helper('cms')->getPageTemplateProcessor()->filter(
                            Mage::getModel('cms/block')->load($rule['price_on_product_list'])
                                ->getContent()
                        );
                    }
                    $priceSelector = $this->_getHelper()->getCssSelector('product_list_price');
                }

                $processor->replace($priceSelector, $replace);
                $output = $processor->getHtml();
            }
        }

        return $output;
    }

    /*
     * check restrictions on product list
     * remove "add to cart" buttons
     */
    protected function _processCatalogProductList($output, $block) {
        if ($category = Mage::registry('current_category')) {
            $categoryId = Mage::registry('current_category')->getId();
        } else {
            $categoryId = -1;
        }
        $activeRules = $this->_getHelper()->getActiveRules(array('hide_price = 1'));
        if (empty($activeRules)) {
            return $output;
        }

        /*
         * apply rules for product
         */
        /** @var $processor Amasty_Groupcat_Model_Htmlprocessor_Interface */
        $processor = Mage::getModel('amgroupcat/htmlprocessor_factory')->createProcessor();
        $processor->load($output);

        foreach ($activeRules as $rule) {
            $ruleCategories = Mage::helper('amgroupcat')->getRestrictedCategories($rule);
            if (in_array($categoryId, $ruleCategories) && $rule['hide_price']) {
                if ($rule['price_on_product_list']) {
                    $replace = Mage::helper('cms')->getPageTemplateProcessor()->filter(
                        Mage::getModel('cms/block')
                                ->load($rule['price_on_product_list'])
                                ->getContent()
                    );
                } else {
                    $replace = ' ';
                }
                $priceSelector  = $this->_getHelper()->getCssSelector('product_list_price');
                $processor->replace($priceSelector, $replace);

                $buttonSelector = $this->_getHelper()->getCssSelector('product_list_addtocart');
                $processor->remove($buttonSelector);

                $output = $processor->getHtml();
            }
        }
/*
        $collection = $block->getLoadedProductCollection();
        foreach( $collection as $product ) {
            $result = $processor->query('#amgroupcat-hided-' . $product->getId());

            foreach ($result as $i => $element) {
                $processor->setCurDomNode($element->parentNode->parentNode);

                $processor->removeInner($buttonSelector);
                if (!is_null($data1))
                {
                    $processor->remove($buttonSelector);
                }

                $processor->setCurDomNode(null);


            }

           // $processor->remove($buttonSelector);

        }*/


        return $output;
    }

    /**
     * @return Amasty_Groupcat_Helper_Data
     */
    protected function _getHelper() {
        return Mage::helper('amgroupcat');
    }

    protected function _getButtonHideHtml($productId) {
        $productId = 'amgroupcat-hided-' . $productId;
        $parent = $this->_getHelper()->getCssSelector('product_list_cell');
        $buttonSelector = $this->_getHelper()->getCssSelector('product_list_addtocart');
        $js = "
            <script type='text/javascript'>
                document.observe('dom:loaded', function() {
                    try{
                        var button = $('" . $productId . "').up('" . $parent . "').select('" . $buttonSelector . "');
                        button.each(function(item){
                            item.remove();
                        });
                    }catch(ex){
                        console.log('Please configure Amasty Group Catalog Selectors.')
                        console.log(ex);
                    }
                });
            </script>
        ";
        return '<div style="display: none;" id="' . $productId . '"></div>' . $js;
    }
}
