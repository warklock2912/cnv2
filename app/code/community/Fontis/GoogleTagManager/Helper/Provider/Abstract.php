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
 * @copyright  Copyright (c) 2014 Fontis Pty. Ltd. (http://www.fontis.com.au)
 * @license    Fontis Software License
 */

abstract class Fontis_GoogleTagManager_Helper_Provider_Abstract extends Mage_Core_Helper_Abstract
{
    /**
     * @var string
     */
    protected $_enabledFlag = '';

    /**
     * @var bool|string
     */
    protected $_pageType = false;

    const PAGETYPE_HOME             = 'home';
    const PAGETYPE_CATEGORY         = 'category';
    const PAGETYPE_PRODUCT          = 'product';
    const PAGETYPE_CART             = 'cart';
    const PAGETYPE_CHECKOUT         = 'checkout';
    const PAGETYPE_CHECKOUT_SUCCESS = 'purchase';
    const PAGETYPE_OTHER            = 'other';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag('fontis_gtm/settings/' . $this->_enabledFlag);
    }

    /**
     * Returns true if visitor data is to be included in the data layer.
     *
     * @return bool
     */
    public function includeVisitorData()
    {
        return Mage::getStoreConfigFlag('fontis_gtm/settings/datalayervisitors');
    }

    /**
     * Returns true if ecommerce data is to be included in the data layer.
     *
     * @return bool
     */
    public function includeEcommerceData()
    {
        return Mage::getStoreConfigFlag('fontis_gtm/settings/datalayerecommerce');
    }

    /**
     * Returns the type of ecommerce data that is to be included in the data
     * layer (provided that the ecommerce data is enabled).
     *
     * @return string
     */
    public function getEcommerceDataType()
    {
        return Mage::getStoreConfig('fontis_gtm/settings/datalayerecommercetype');
    }

    /**
     * Return the current page type, generated from the current module, controller and action.
     *
     * Supports cart, onepage checkout, checkout success, homepage, category page, product page and other page.
     *
     * Uses the front controller instance rather than the module, as this should guard against most forms of rewriting.
     *
     * @return string
     */
    protected function _getPageType()
    {
        if (!$this->_pageType) {
            $instance = Mage::app()->getFrontController()->getAction();
            $request = Mage::app()->getRequest();
            $action = $request->getActionName();
            $currentUrl = Mage::getUrl('*/*/*', array('_current' => true, '_use_rewrite' => true));

            if ($instance instanceof Mage_Checkout_CartController) {
                $this->_pageType = self::PAGETYPE_CART;
            } elseif ($instance instanceof Mage_Checkout_Controller_Action) {
                if ($action == 'index') {
                    $this->_pageType = self::PAGETYPE_CHECKOUT;
                } elseif ($action == 'success') {
                    $this->_pageType = self::PAGETYPE_CHECKOUT_SUCCESS;
                }
            } elseif (Mage::helper('checkout/url')->getCheckoutUrl() == $currentUrl) {
                $this->_pageType = self::PAGETYPE_CHECKOUT;
            } elseif (Mage::getUrl('') == $currentUrl) {
                $this->_pageType = self::PAGETYPE_HOME;
            } elseif ($instance instanceof Mage_Catalog_CategoryController && $action == 'view') {
                $this->_pageType = self::PAGETYPE_CATEGORY;
            } elseif ($instance instanceof Mage_Catalog_ProductController && $action == 'view') {
                $this->_pageType = self::PAGETYPE_PRODUCT;
            } else {
                $this->_pageType = self::PAGETYPE_OTHER;
            }
        }

        return $this->_pageType;
    }

    /**
     * Add the parent SKU associated with the parent quote/order item.
     *
     * Due to the child SKU always being stored on the parent and child items,
     * the parent SKU needs to be retrieved manually.
     *
     * @param Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item $item
     * @param array $productData
     * @param string $key
     * @return array
     */
    public function addParentItemSku($item, array $productData, $key = 'parent_sku')
    {
        if ($item->getProductType() === Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
            || $item->getProductType() === Mage_Catalog_Model_Product_Type::TYPE_BUNDLE
        ) {
            /** @var Mage_Catalog_Model_Resource_Product $productResourceModel */
            $productResourceModel = Mage::getResourceModel('catalog/product');
            $resultSku = $productResourceModel->getProductsSku(array($item->getProductId()));

            if (empty($resultSku)) {
                $productData[$key] = '';
            } else {
                $productData[$key] = $resultSku[0]['sku'];
            }
        } else {
            $productData[$key] = $item->getSku();
        }

        return $productData;
    }
}
