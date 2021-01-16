<?php

/*

Plumrocket Inc.

NOTICE OF LICENSE

This source file is subject to the End-user License Agreement
that is available through the world-wide-web at this URL:
http://wiki.plumrocket.net/wiki/EULA
If you are unable to obtain it through the world-wide-web, please
send an email to support@plumrocket.com so we can send you a copy immediately.

@package	Plumrocket_Cart_Reservation-v1.5.x
@copyright	Copyright (c) 2013 Plumrocket Inc. (http://www.plumrocket.com)
@license	http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 
*/

class Plumrocket_Cartreservation_Model_Observer
{
    
    public function removeLostReserveItemsForCustomer(Varien_Event_Observer $observer)
    {
        // If admin, not check customer reservation.
        // First we need set custom customer session below and next call this function
        return (Mage::helper('cartreservation')->moduleSessionEnabled() && (! Mage::app()->getStore()->isAdmin()))
            ? Mage::getSingleton('cartreservation/observerEncoded')->removeLostReserveItemsForCustomer($observer)
            : $observer;
    }

    public function beforeOrderSave(Varien_Event_Observer $observer)
    {
        return (Mage::helper('cartreservation')->moduleSessionEnabled())
            ? Mage::getSingleton('cartreservation/observerEncoded')->beforeOrderSave($observer)
            : $observer;
    }

    public function orderSave(Varien_Event_Observer $observer)
    {
        return (Mage::helper('cartreservation')->moduleSessionEnabled())
            ? Mage::getSingleton('cartreservation/observerEncoded')->orderSave($observer)
            : $observer;
    }
    
    public function mergeCart(Varien_Event_Observer $observer)
    {
        return (Mage::helper('cartreservation')->moduleSessionEnabled())
            ? Mage::getSingleton('cartreservation/observerEncoded')->mergeCart($observer)
            : $observer;
    }

    public function productsAreSalable(Varien_Event_Observer $observer)
    {
        return (Mage::helper('cartreservation')->moduleEnabled())
            ? Mage::getSingleton('cartreservation/observerEncoded')->productsAreSalable($observer)
            : $observer;
    }
    
    public function productIsSalable(Varien_Event_Observer $observer)
    {
        return (Mage::helper('cartreservation')->moduleEnabled())
            ? Mage::getSingleton('cartreservation/observerEncoded')->productIsSalable($observer)
            : $observer;
    }

    public function initAdminSession(Varien_Event_Observer $observer)
    {
        if (Mage::helper('cartreservation')->moduleEnabled()) {
            $sessionQuote = $observer->getEvent()->getSessionQuote();

            if ($customerId = $sessionQuote->getCustomerId()) {
                Mage::helper('cartreservation/customer')->setCustomId($customerId);
                Mage::getSingleton('cartreservation/observerEncoded')->removeLostReserveItemsForCustomer($observer);
            }
        }

        return $observer;
    }

    public function mergeCartSales(Varien_Event_Observer $observer)
    {
        if (Mage::helper('cartreservation')->moduleSessionEnabled()) {
            $customerCart = $observer->getEvent()->getOrderCreateModel()->getCustomerCart();
            if ($customerCart->getId()) {
                Mage::getSingleton('cartreservation/observerEncoded')->mergeCartWithQuote($customerCart);
            }
        }

        return $observer;
    }

    public function deleteCartItemFromAdmin(Varien_Event_Observer $observer)
    {
        $request = $observer->getEvent()->getControllerAction()->getRequest();
        $websiteId = $request->getParam('website_id');
        $deleteItemId = $request->getPost('delete');

        if (Mage::registry('current_customer') && $deleteItemId) {
            Mage::helper('cartreservation/customer')->setCustomId(Mage::registry('current_customer')->getId());

            $quote = Mage::getModel('sales/quote')
                ->setWebsite(Mage::app()->getWebsite($websiteId))
                ->loadByCustomer(Mage::registry('current_customer')); 

            Mage::getSingleton('cartreservation/observerEncoded')->mergeCartWithQuote($quote);
        }
    }

    public function mergeEnterpriseCustomerCart(Varien_Event_Observer $observer)
    {
        //if (Mage::helper('cartreservation')->moduleSessionEnabled()) {
        if (Mage::helper('cartreservation')->moduleEnabled()) {
            $controller = $observer->getEvent()->getControllerAction();

            if ($controller
                && ($controller->getRequest()->getControllerName() == 'checkout')
                && in_array($controller->getRequest()->getActionName(), array('cart', 'addToCart', 'updateItems')) // index and loadBlock fixed in items.phtml
            ) {
                $customerQuote = $controller->getCartModel()->getQuote();
                Mage::helper('cartreservation')->checkAndMergeCart($customerQuote);
            }
        }

        return $observer;
    }

    public function cleanCache($observer)
    {
        if (Mage::helper('cartreservation')->moduleEnabled()) {
            $item = $observer->getItem();
            if (!$item) {
                $item = $observer->getQuoteItem();
            }

            Mage::helper('cartreservation/product')->cleanCache($item->getProductId());
        }
    }
}
