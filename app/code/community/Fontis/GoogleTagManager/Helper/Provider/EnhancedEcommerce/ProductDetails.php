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

class Fontis_GoogleTagManager_Helper_Provider_EnhancedEcommerce_ProductDetails extends Fontis_GoogleTagManager_Helper_Provider_Abstract
{
    /**
     * @return array
     */
    public function getData()
    {
        $data = array();

        if ($this->_getPageType() == self::PAGETYPE_PRODUCT) {
            /** @var $product Mage_Catalog_Model_Product */
            $product = Mage::registry('current_product');

            /** @var $enhancedEcommerceHelper Fontis_GoogleTagManager_Helper_EnhancedEcommerce */
            $enhancedEcommerceHelper = Mage::helper('googletagmanager/enhancedEcommerce');
            $productData = $enhancedEcommerceHelper->buildProductData($product);

            $data['products'] = array($productData);
        }

        return $data;
    }
}
