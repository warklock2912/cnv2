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

class Plumrocket_Cartreservation_Model_Override_Checkout_SessionEncoded extends Mage_Checkout_Model_Session
{
    public function loadCustomerQuote()
    {
        if (!Mage::getSingleton('customer/session')->getCustomerId()) {
            return $this;
        }

        Mage::dispatchEvent('load_customer_quote_before', array('checkout_session' => $this));

        $customerQuote = Mage::getModel('sales/quote')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomerId());

        if ($customerQuote->getId() && $this->getQuoteId() != $customerQuote->getId()) {
            if ($this->getQuoteId()) {
                $customerQuote->merge($this->getQuote())
                    ->collectTotals()
                    ->save();
                    
                // Added
                if (Mage::helper('cartreservation')->moduleEnabled()) {
                    $this->_afterMergeQuote($customerQuote);
                }
            }

            $this->setQuoteId($customerQuote->getId());

            if ($this->_quote) {
                $this->_quote->delete();
            }

            $this->_quote = $customerQuote;
        } else {
            $this->getQuote()->getBillingAddress();
            $this->getQuote()->getShippingAddress();
            $this->getQuote()->setCustomer(Mage::getSingleton('customer/session')->getCustomer())
                ->setTotalsCollectedFlag(false)
                ->collectTotals()
                ->save();
                
            // Added
            if (Mage::helper('cartreservation')->moduleEnabled()) {
                $this->_changeCustomerForQuote();
            }
        }

        return $this;
    }
    
    private function _afterMergeQuote($customerQuote)
    {
        // If Guest reservation is disabled then we should not prepare data for next merge - all times will start from 0.
        if (Mage::getStoreConfig('cartreservation/general/type') == Plumrocket_Cartreservation_Model_Values_Usertypes::ALL_USERS) {
            $_reserve_items = Mage::getModel('cartreservation/item')->getCollection()
                ->addFieldToFilter('quote_id', $this->getQuote()->getId());
                
            // get old CR items	
            $oldItems = array();
            foreach ($_reserve_items as $item) {
                $oldItems[ $item->getData('quote_item_id') ] = $item;
            }
            
            $customerItems = $customerQuote->getAllItems();
            foreach ($this->getQuote()->getAllItems() as $item) {
                // if in old CR items exists alias for this item
                if (isset($oldItems[ $item->getId() ])) {
                    // Find similar cart items in new cart - then we set old alias data for their new aliases.
                    foreach ($customerItems as $quoteItem) {
                        if ($quoteItem->compare($item)) {
                            $quoteItem->setData('old_reserve_item', $oldItems[ $item->getId() ]);
                            break;
                        }
                    }
                }
            }

            // Prepare data and call event for catch it observer::mergeCart
            $cart = new Varien_Object();
            $cart->setQuote($customerQuote);
            
            Mage::dispatchEvent('checkout_cart_save_after', array('cart'=>$cart));
        }
    }
    
    private function _changeCustomerForQuote()
    {
        // Else CR items will be not exists
        if (Mage::getStoreConfig('cartreservation/general/type') == Plumrocket_Cartreservation_Model_Values_Usertypes::ALL_USERS) {
            $_reserve_items = Mage::getModel('cartreservation/item')->getCollection()
                ->addFieldToFilter('quote_id', $this->getQuote()->getId());
            
            // Quote id will be the same as guest's thus we should update just customer id
            foreach ($_reserve_items as $item) {
                $item->setData('customer_id', (int)Mage::getSingleton('customer/session')->getCustomerId());
                $item->setData('session_id', Mage::helper('cartreservation/customer')->getSessionId());
                $item->save();

                // Log:
                    Mage::helper('cartreservation')->logAction('import_guest', $item->getData());
            }
        }
    }
}    