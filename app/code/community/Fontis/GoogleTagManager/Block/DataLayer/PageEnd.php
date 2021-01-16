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

class Fontis_GoogleTagManager_Block_DataLayer_PageEnd extends Fontis_GoogleTagManager_Block_DataLayer
{
    /**
     * Generate JavaScript for the data layer.
     *
     * @return string|null
     */
    protected function _getDataLayer()
    {
        // Initialise our data source.
        $data = $this->_getPageData();

        // Generate the data layer JavaScript.
        if (!empty($data)) {
            return "<script>window.dataLayer = window.dataLayer || [];\nwindow.dataLayer.push(" . Zend_Json::encode($data) . ");</script>\n";
        } else {
            return '';
        }
    }

    /**
     * Get page data for use in the data layer.
     *
     * This is only for pageData that needs to be generated after the page content.
     *
     * @return array
     */
    protected function _getPageData()
    {
        $data = array();

        if (!Mage::getStoreConfigFlag(Fontis_GoogleTagManager_Helper_Provider_Categories::VIEWLIST_CONFIG)) {
            return $data;
        }

        /** @var Fontis_GoogleTagManager_Helper_Data $helper */
        $helper = Mage::helper('googletagmanager');
        if ($helper->getPageType() != 'catalog') {
            return $data;
        }

        /** @var Mage_Catalog_Model_Category $category */
        $category = Mage::registry('current_category');
        if (!$category) {
            return $data;
        }

        // This category page is a landing page with no products
        if ($category->getDisplayMode() == Mage_Catalog_Model_Category::DM_PAGE) {
            return $data;
        }

        /** @var Mage_Catalog_Model_Layer $layer */
        $layer = Mage::registry('current_layer');
        if ($layer === null) {
            $layer = Mage::getSingleton('catalog/layer');
        }

        // Don't want to operate on the actual product collection,
        // as this is used to build the category page. If we apply
        // a LIMIT to the original collection, we'll limit how
        // many products are shown on category pages.
        /** @var Mage_Catalog_Model_Product[] $collection */
        $collection = clone $layer->getProductCollection();

        $count = 0;
        foreach ($collection as $product) {
            if ($count > 2) {
                break;
            }

            $data['viewList'][] = $product->getSku();

            $count++;
        }

        return $data;
    }

    /**
     * Render Google Tag Manager code.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (Mage::helper('googletagmanager')->isGtmEnabled()) {
            return $this->_getDataLayer();
        }  else {
            return $this->getEmptyContent();
        }
    }
}
