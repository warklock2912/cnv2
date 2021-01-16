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

class Fontis_GoogleTagManager_Helper_Provider_Personal extends Fontis_GoogleTagManager_Helper_Provider_Abstract
{
    const HASHEDEMAIL_DISABLED = 'disabled';
    const HASHEDEMAIL_SHA1 = 'sha1';
    const HASHEDEMAIL_MD5 = 'md5';
    const HASHEDEMAIL_DATALAYER = 'emailHash';
    const HASHEDEMAIL_CONFIG = 'fontis_gtm/settings/hashedemail';

    /**
     * Gather data for the data layer.
     *
     * @return string|null
     */
    public function getDataLayer()
    {
        // Initialise our data source.
        $data = array();

        // Get ecommerce and visitor data, if desired.
        if ($this->includeEcommerceData()) {
            if ($ecommerceData = $this->_getEcommerceData()) {
                $data = $data + $ecommerceData;
            }
        }
        if ($this->includeVisitorData()) {
            $data = $data + $this->_getVisitorData();
        }

        if (!empty($data)) {
            return $data;
        } else {
            return null;
        }
    }

    /**
     * Get ecommerce (order) data for use in the data layer.
     *
     * @link https://developers.google.com/tag-manager/reference
     * @return array
     */
    protected function _getEcommerceData()
    {
        if ($this->_getPageType() == self::PAGETYPE_CHECKOUT_SUCCESS) {
            // Is a checkout success page
            $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        } else {
            return false;
        }

        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

        $ecommerceDataType = $this->getEcommerceDataType();
        $ecommerceData = array();

        if ($ecommerceDataType == Fontis_GoogleTagManager_Model_Source_EcommerceType::ECOMMERCE_TYPE_STANDARD) {
            $ecommerceData = $this->_getGoogleAnalyticsEcommerceData($order);
        } else if ($ecommerceDataType == Fontis_GoogleTagManager_Model_Source_EcommerceType::ECOMMERCE_TYPE_ENHANCED) {
            $ecommerceData = $this->_getGoogleAnalyticsEnhancedEcommerceData($order);
        }

        $data = array_merge($ecommerceData, $this->_getExtraData($order));

        return $data;
    }

    /**
     * Get data required for Google Analytics Ecommerce.
     *
     * See https://support.google.com/tagmanager/answer/6106097 for a
     * description of the fields returned from this function.
     *
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function _getGoogleAnalyticsEcommerceData(Mage_Sales_Model_Order $order)
    {
        $orderItems = $this->_getOrderItems($order);
        $products = $this->_processOrderItemsEcommerce($orderItems);

        $data = array(
            'transactionId'          => $order->getIncrementId(),
            'transactionDate'        => date("Y-m-d", Mage::getModel('core/date')->timestamp(time())),
            'transactionAffiliation' => Mage::helper('googletagmanager')->getTransactionAffiliation(),
            'transactionTotal'       => round($order->getBaseGrandTotal(), 2),
            'transactionTax'         => round($order->getBaseTaxAmount(), 2),
            'transactionPaymentType' => $order->getPayment()->getMethodInstance()->getTitle(),
            'transactionCurrency'    => $order->getOrderCurrencyCode(),
            'transactionPromoCode'   => $order->getCouponCode(),
            'transactionProducts'    => $products,
        );

        if ($order->getIsNotVirtual()) {
            $carrier = $order->getShippingCarrier();
            $carrierCode = null;
            if ($carrier) {
                $carrierCode = $carrier->getCarrierCode();
            } else {
                Mage::log("Couldn't find a carrier code for order ID: " . $order->getIncrementId()
                    . " when building GTM checkout success dataLayer.");
            }

            $data = array_merge($data, array(
                'transactionShipping'       => round($order->getBaseShippingAmount(), 2),
                'transactionShippingMethod' => $carrierCode,
            ));
        }

        // Trim empty fields from the final output.
        foreach ($data as $key => $value) {
            if (!is_numeric($value) && empty($value)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Processes each of the order items, and returns the product data needed
     * for Google Analytics Ecommerce.
     *
     * See https://developers.google.com/analytics/devguides/collection/analyticsjs/ecommerce#item
     * for a description of the fields returned from this function.
     *
     * @param array $orderItems
     * @return array
     */
    protected function _processOrderItemsEcommerce($orderItems)
    {
        $products = array();

        foreach ($orderItems as $itemData) {
            /** @var Mage_Sales_Model_Order_Item $item */
            $item = $itemData['item'];
            $quantity = $itemData['quantity'];

            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $item->getProduct()->getSku());

            $productCategories = $product->getCategoryIds();
            $categories = array();
            foreach ($productCategories as $category) {
                $categories[] = Mage::getModel('catalog/category')->load($category)->getName();
            }

            $productData = array(
                'name'     => $item->getName(),
                'sku'      => $item->getSku(),
                'category' => implode('|', $categories),
                'price'    => (double)number_format($item->getOriginalPrice(), 2, '.', ''),
                'quantity' => $quantity,
            );

            $productData = $this->addParentItemSku($item, $productData, 'parentSku');
            $products[] = $productData;
        }

        return $products;
    }

    /**
     * Get data required for Google Analytics Enhanced Ecommerce.
     *
     * See https://developers.google.com/tag-manager/enhanced-ecommerce#purchases
     * for a description of the fields returned by this function.
     *
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function _getGoogleAnalyticsEnhancedEcommerceData(Mage_Sales_Model_Order $order)
    {
        $orderItems = $this->_getOrderItems($order);
        $products = $this->_processOrderItemsEnhancedEcommerce($orderItems);

        $couponCode = '';

        if ($order->getCouponCode()) {
            $couponCode = $order->getCouponCode();
        }

        /** @var $eeHelper Fontis_GoogleTagManager_Helper_EnhancedEcommerce */
        $eeHelper = Mage::helper('googletagmanager/enhancedEcommerce');

        $actionField = array(
            'id'          => $order->getIncrementId(),
            'affiliation' => Mage::helper('googletagmanager')->getTransactionAffiliation(),
            'revenue'     => $eeHelper->formatPrice($order->getBaseGrandTotal()),
            'tax'         => $eeHelper->formatPrice($order->getBaseTaxAmount()),
            'coupon'      => $couponCode,
        );

        if ($order->getIsNotVirtual()) {
            $actionField = array_merge($actionField, array(
                'shipping' => $eeHelper->formatPrice($order->getBaseShippingAmount()),
            ));
        }

        $data = array(
            'ecommerce' => array(
                'currencyCode' => $order->getOrderCurrencyCode(),
                'purchase'     => array(
                    'actionField' => $actionField,
                    'products'    => $products
                )
            )
        );

        return $data;
    }

    /**
     * Processes each of the order items, and returns the product data needed
     * for Google Analytics Enhanced Ecommerce.
     *
     * See https://developers.google.com/analytics/devguides/collection/analyticsjs/enhanced-ecommerce#product-data
     * for a description of the fields returned from this function.
     *
     * @param array $orderItems
     * @return array
     */
    protected function _processOrderItemsEnhancedEcommerce($orderItems)
    {
        $products = array();

        foreach ($orderItems as $itemData) {
            /** @var Mage_Sales_Model_Order_Item $item */
            $item = $itemData['item'];
            $quantity = $itemData['quantity'];

            $product = $item->getProduct();

            /** @var $eeHelper Fontis_GoogleTagManager_Helper_EnhancedEcommerce */
            $eeHelper = Mage::helper('googletagmanager/enhancedEcommerce');
            $productData = $eeHelper->buildProductData($product);

            $productData['name'] = $item->getName();
            $productData['price'] = $eeHelper->formatPrice($item->getOriginalPrice());
            $productData['quantity'] = $quantity;

            $variantAttributeCode = $eeHelper->getVariantSourceAttribute();
            if ($eeHelper->includeVariantData() && $product->hasData($variantAttributeCode)) {
                $productData['variant'] = $product->getResource()
                    ->getAttribute($variantAttributeCode)->getFrontend()->getValue($product);
            }

            $products[] = $productData;
        }

        return $products;
    }

    /**
     * Returns a list of the visible products attached to an order, with
     * duplicate items merged.
     *
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function _getOrderItems(Mage_Sales_Model_Order $order)
    {
        $orderItems = array();

        foreach ($order->getAllVisibleItems() as $item) {
            if (empty($orderItems[$item->getSku()])) {
                $orderItems[$item->getSku()] = array(
                    'item'     => $item,
                    'quantity' => (int)$item->getQtyOrdered(),
                );
            } else {
                // If we already have the item, update quantity.
                $orderItems[$item->getSku()]['quantity'] += (int)$item->getQtyOrdered();
            }
        }

        return $orderItems;
    }

    /**
     * Gets any extra personal data.
     *
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function _getExtraData(Mage_Sales_Model_Order $order)
    {
        return array(
            'transactionQuoteId' => $order->getQuoteId(),
        );
    }

    /**
     * Get visitor data for use in the data layer.
     *
     * @link https://developers.google.com/tag-manager/reference
     * @return array
     */
    protected function _getVisitorData()
    {
        $items = Mage::getConfig()->getNode('frontend/personaldataLayer')->asArray();

        $pageArray = array();
        foreach ($items as $key => $value) {
            $pageArray[$key] = Mage::helper($value['helper'])->{$value['function']}();
        }

        $customer = Mage::getSingleton('customer/session');

        if ($customer->isLoggedIn()) {
            $data = Mage::helper('tokens')->tokeniseArray($pageArray);
        } else {
            $data = array();
        }

        // Include hashed email
        $hashedEmailSetting = Mage::getStoreConfig(Fontis_GoogleTagManager_Helper_Provider_Personal::HASHEDEMAIL_CONFIG);
        if ($hashedEmailSetting
            && $hashedEmailSetting !== Fontis_GoogleTagManager_Helper_Provider_Personal::HASHEDEMAIL_DISABLED
            && ($customerEmail = $customer->getCustomer()->getEmail())) {
            $data[Fontis_GoogleTagManager_Helper_Provider_Personal::HASHEDEMAIL_DATALAYER] = $this->_getHashedEmail(
                $hashedEmailSetting, $customerEmail
            );
        }

        // visitorId
        if ($customer->getCustomerId()) {
            $data['visitorId'] = (string) $customer->getCustomerId();
        }

        // visitorLoginState
        $data['visitorLoginState'] = ($customer->isLoggedIn()) ? 'Logged in' : 'Logged out';

        // visitorType
        $data['visitorType'] = (string)Mage::getModel('customer/group')->load($customer->getCustomerGroupId())->getCode();

        // visitorExistingCustomer / visitorLifetimeValue
        $orders = Mage::getResourceModel('sales/order_collection')->addFieldToSelect('*')->addFieldToFilter('customer_id', $customer->getId());
        $ordersTotal = 0;
        foreach ($orders as $order) {
            $ordersTotal += $order->getGrandTotal();
        }
        $data['visitorLifetimeValue'] = round($ordersTotal, 2);
        $data['visitorExistingCustomer'] = ($ordersTotal > 0) ? 'Yes' : 'No';

        return $data;
    }

    /**
     * Return the hashed version of an email.
     *
     * The type of hash used is determined by the $hashedEmailSetting value.
     *
     * @param string $hashedEmailSetting
     * @param string $customerEmail
     * @return string
     */
    protected function _getHashedEmail($hashedEmailSetting, $customerEmail)
    {
        if (empty($customerEmail)) {
            return null;
        }

        if ($hashedEmailSetting === Fontis_GoogleTagManager_Helper_Provider_Personal::HASHEDEMAIL_SHA1) {
            return hash('sha512', $customerEmail);
        } else if ($hashedEmailSetting === Fontis_GoogleTagManager_Helper_Provider_Personal::HASHEDEMAIL_MD5) {
            return hash('md5', $customerEmail);
        }

        return null;
    }
}
