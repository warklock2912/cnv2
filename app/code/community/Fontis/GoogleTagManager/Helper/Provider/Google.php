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

class Fontis_GoogleTagManager_Helper_Provider_Google extends Fontis_GoogleTagManager_Helper_Provider_Abstract
{
    /**
     * @var string
     */
    protected $_enabledFlag = 'datalayergoogle';

    /**
     * @var array
     */
    protected $_cartProducts = array();

    /**
     * @var array
     */
    protected $_orderProducts = array();

    /**
     * Return all the information to be included in the datalayer.
     *
     * @return array
     */
    public function getData()
    {
        $pageType = $this->_getPageType();

        $data = array(
            'parent_sku'           => $this->_getParentSku(),       // array or individual product, dependent on page
            'child_sku'            => $this->_getChildSku(),        // array or individual product, dependent on page
            'ecomm_prodid'        => $this->_getProductId(),      // array or individual product, dependent on page
            'ecomm_pagetype'      => $pageType,                   // home, category, product, cart, purchase (success), checkout, other
            'ecomm_pname'         => $this->_getProductName(),    // array or individual product names
            'product_value'        => $this->_getProductPrice(),   // array or individual product values
            'ecomm_totalvalue'    => $this->_getTotalValue(),     // total value of the products
        );

        if ($pageType == self::PAGETYPE_CART ||
            $pageType == self::PAGETYPE_CHECKOUT ||
            $pageType == self::PAGETYPE_CHECKOUT_SUCCESS
        ) {
            $data['quantity'] = $this->_getProductQty(); // array or individual product qty
        }

        return $data;
    }

    /**
     * Return the relevant parent product SKU parameter for this page.
     *
     * For a configurable product, this would be the product's SKU. For a simple product, this would be the SKU of
     * the associated parent configurable product if it has one.
     *
     * If we're in the checkout process or shopping cart, this will be an array of all item SKUs in the cart, and for
     * all other pages it will be empty.
     *
     * @return array
     */
    protected function _getParentSku()
    {
        switch ($this->_getPageType()) {
            case self::PAGETYPE_CART:
            case self::PAGETYPE_CHECKOUT:
                return $this->_getItemAttributes($this->_getCartProducts(), 'parent_sku');

            case self::PAGETYPE_CHECKOUT_SUCCESS:
                return $this->_getItemAttributes($this->_getOrderProducts(), 'parent_sku');

            default:
                return array();
        }
    }

    /**
     * Return the relevant child product SKU parameter for this page.
     *
     * If we're in the checkout process or shopping cart, this will be an array of all item SKUs in the cart, and for
     * all other pages it will be empty.
     *
     * @return array|string
     */
    protected function _getChildSku()
    {
        switch ($this->_getPageType()) {
            case self::PAGETYPE_CART:
            case self::PAGETYPE_CHECKOUT:
                return $this->_getItemAttributes($this->_getCartProducts(), 'sku');

            case self::PAGETYPE_CHECKOUT_SUCCESS:
                return $this->_getItemAttributes($this->_getOrderProducts(), 'sku');

            default:
                return array();
        }
    }

    /**
     * Return the relevant product ID parameter for this page.
     *
     * If we're in the checkout process, this will be an array of all item SKUs in the cart. For a product page this
     * will be the product SKU, and for all other pages it will be empty.
     *
     * @return array|string
     */
    protected function _getProductId()
    {
        switch ($this->_getPageType()) {
            case self::PAGETYPE_PRODUCT:
                return Mage::registry('current_product')->getSku();

            case self::PAGETYPE_CART:
            case self::PAGETYPE_CHECKOUT:
                return $this->_getItemAttributes($this->_getCartProducts(), 'sku');

            case self::PAGETYPE_CHECKOUT_SUCCESS:
                return $this->_getItemAttributes($this->_getOrderProducts(), 'sku');

            default:
                return '';
        }
    }

    /**
     * Return the relevant product name parameter for this page.
     *
     * This will be an array for the checkout process, a string for a product page or an empty string otherwise.
     *
     * @return array|string
     */
    protected function _getProductName()
    {
        switch ($this->_getPageType()) {
            case self::PAGETYPE_PRODUCT:
                return Mage::registry('current_product')->getName();

            case self::PAGETYPE_CART:
            case self::PAGETYPE_CHECKOUT:
                return $this->_getItemAttributes($this->_getCartProducts(), 'name');

            case self::PAGETYPE_CHECKOUT_SUCCESS:
                return $this->_getItemAttributes($this->_getOrderProducts(), 'name');

            default:
                return '';
        }
    }

    /**
     * Return the product price parameter for the current page.
     *
     * This will be formatted into two decimal places. For a product page, it's a single price. An array of all prices
     * for checkout process pages, and an empty string for everything else.
     *
     * @return array|number|string
     */
    protected function _getProductPrice()
    {
        switch ($this->_getPageType()) {
            case self::PAGETYPE_PRODUCT:
                return Mage::helper('tokens/provider_other')->getProductPrice(Mage::registry('current_product'));

            case self::PAGETYPE_CART:
            case self::PAGETYPE_CHECKOUT:
                return $this->_getItemAttributes($this->_getCartProducts(), 'price');

            case self::PAGETYPE_CHECKOUT_SUCCESS:
                return $this->_getItemAttributes($this->_getOrderProducts(), 'price');

            default:
                return '';
        }
    }

    /**
     * Get the product quantity parameter.
     *
     * Should only be called on checkout process pages, and is an array of products in the users cart.
     *
     * @return array|string
     */
    protected function _getProductQty()
    {
        switch ($this->_getPageType()) {
            case self::PAGETYPE_CART:
            case self::PAGETYPE_CHECKOUT:
                return $this->_getItemAttributes($this->_getCartProducts(), 'qty');

            case self::PAGETYPE_CHECKOUT_SUCCESS:
                return $this->_getItemAttributes($this->_getOrderProducts(), 'qty');

            default:
                return '';
        }
    }

    /**
     * Get the total value of items on the page.
     *
     * Is the price of the product if it's a product page; for checkout process items it's the grand total of the cart.
     *
     * @return number|string
     */
    protected function _getTotalValue()
    {
        switch ($this->_getPageType()) {
            case self::PAGETYPE_PRODUCT:
                return Mage::helper('tokens/provider_other')->getProductPrice(Mage::registry('current_product'));

            case self::PAGETYPE_CART:
            case self::PAGETYPE_CHECKOUT:
                return (float)number_format(Mage::getSingleton('checkout/cart')->getQuote()->getGrandTotal(), 2, '.', '');

            case self::PAGETYPE_CHECKOUT_SUCCESS:
                $order = Mage::getModel('sales/order')->load(Mage::getSingleton('checkout/session')->getLastOrderId());
                return (float)number_format($order->getGrandTotal(), 2, '.', '');

            default:
                return '';
        }
    }

    /**
     * Get all the products in the cart.
     *
     * We build an array of the sku, qty, price and name of each product in the cart, then cache the result and use it
     * in further requests. The parent product SKU will also be included if the purchased product is a child of another.
     *
     * @return array
     */
    protected function _getCartProducts()
    {
        if (empty($this->_cartProducts)) {
            /** @var Mage_Sales_Model_Quote_Item $item */
            foreach (Mage::getSingleton('checkout/cart')->getQuote()->getAllVisibleItems() as $item) {
                $cartItemValues = array(
                    'sku'   => $item->getProduct()->getSku(),
                    'qty'   => $item->getQty(),
                    'price' => (float)number_format($item->getPriceInclTax(), 2, '.', ''),
                    'name'  => $item->getName()
                );

                $cartItemValues = $this->addParentItemSku($item, $cartItemValues);
                $this->_cartProducts[] = $cartItemValues;
            }
        }

        return $this->_cartProducts;
    }

    /**
     * Return an array of the sku, qty, price and name of each product used in the last order on this session.
     *
     * This should only be called on the checkout success page.
     *
     * @return array
     */
    protected function _getOrderProducts()
    {
        if (empty($this->_orderProducts)) {
            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::getModel('sales/order')->load(Mage::getSingleton('checkout/session')->getLastOrderId());

            /** @var Mage_Sales_Model_Order_Item $item */
            foreach ($order->getAllVisibleItems() as $item) {
                $orderItemValues = array(
                    'sku'   => $item->getSku(),
                    'qty'   => $item->getQtyOrdered(),
                    'price' => (float)number_format($item->getOriginalPrice(), 2, '.', ''),
                    'name'  => $item->getName()
                );

                $orderItemValues = $this->addParentItemSku($item, $orderItemValues);
                $this->_orderProducts[] = $orderItemValues;
            }
        }

        return $this->_orderProducts;
    }

    /**
     * Iterate through an array of products, and return a particular array value for each.
     *
     * @param array $items
     * @param string $attribute
     * @return array
     */
    protected function _getItemAttributes($items, $attribute)
    {
        $data = array();

        foreach ($items as $item) {
            $data[] = isset($item[$attribute]) ? $item[$attribute] : "";
        }

        return $data;
    }
}
