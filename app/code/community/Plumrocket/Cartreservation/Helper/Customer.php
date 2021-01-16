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

class Plumrocket_Cartreservation_Helper_Customer extends Plumrocket_Cartreservation_Helper_Main
{
    private $_userItems = array();
    private $_called_userItems = false;
    private $_customId = null;

    public function getReservedCount($productId, $limit = 0)
    {
        // If cart item reserved 20 peoples and qtu = 10, then 10 first peoples must get access to buy
        // $items - array of CR items.
        $items = Mage::helper('cartreservation/product')->getReservedItems($productId, $limit);
        $count = 0;

        $customerId = $this->getId();
        $sessionId = $this->getSessionId();

        foreach ($items as $item) {
            if ($customerId) {
                if ($item->getData('customer_id') == $customerId) {
                    $count += (int)$item->getQty();
                }
            } elseif ($item->getData('session_id') == $sessionId) {
                $count += (int)$item->getQty();
            }
        }

        return $count;
    }

    public function getItems()
    {
        // Fix for Sql updater: Magento admin panel run it.
        if (! (Mage::app()->getStore()->isAdmin()
                // if was set custom id then it migth be under admin
                && is_null($this->_customId)
            )
            && !$this->_called_userItems
        ) {
            $this->_userItems = array();

            $cid = $this->getId();
            $data = ($cid > 0)
                ? Mage::getModel('cartreservation/item')->getCollection()->addFieldToFilter('customer_id', $cid)
                : Mage::getModel('cartreservation/item')->getCollection()->addFieldToFilter('session_id', $this->getSessionId());

            foreach ($data as $item) {
                $this->_userItems[ $item->getQuoteItemId() ] = $item;
            }

            $this->_called_userItems = true;
        }

        return $this->_userItems;
    }

    public function hasItems()
    {
        return count($this->getItems()) > 0;
    }

    public function resetItems()
    {
        $this->_called_userItems = false;
    }

    public function leftReservationTime()
    {
        $time = 0;
        $items = $this->getItems();

        foreach ($items as $item) {
            $itemTime = (int)$item->leftReservationTime();
            if ($itemTime > $time) {
                $time = $itemTime;
            }
        }

        return $time;
    }

    public function getItemsWithExpiredReminderTime($source)
    {
        $items = $this->getItems();
        $result = array();

        foreach ($items as $item) {
            if ($item->leftReminderTime($source) === 0) {
                $result[] = $item;
            }
        }

        return $result;
    }

    public function expireItemsWithExpiredReminderTime($source)
    {
        $items = $this->getItemsWithExpiredReminderTime($source);
        foreach ($items as $item) {
            $item->setData($source . 'ed', 1)->save();
        }
    }

    public function leftReminderTime($source = 'email')
    {
        $time = $this->leftReservationTime();
        $done = false;
        $items = $this->getItems();

        foreach ($items as $item) {
            $itemTime = $item->leftReminderTime($source);
            // ignore alerted or emailed (depended on $source)
            if (($itemTime !== false) && ((int)$itemTime < $time)) {
                $time = (int)$itemTime;
                $done = true;
            }
        }

        return ($done)? $time: false;
    }

    /*
	 * Reset reservation time
	 */
    public function resetReservationTime($type = 'all')
    {
        $items = $this->getItems();
        foreach ($items as $item) {
            $item->resetReservationTime($type);
        }

        $this->resetItems();
    }

    public function setCustomId($id)
    {
        $this->_customId = $id;
    }

    public function getId()
    {
        if (!is_null($this->_customId)) {
            return $this->_customId;
        }

        return (! Mage::app()->getStore()->isAdmin()
            && Mage::getSingleton('customer/session')->isLoggedIn()
        )?
            Mage::getSingleton('customer/session')->getCustomer()->getId():
            false;
    }

    public function getSessionId()
    {
        $result = '';
        if (! Mage::app()->getStore()->isAdmin()) {
            if (isset($_COOKIE['frontend'])) {
                $result = $_COOKIE['frontend'];
            } elseif (isset($_COOKIE['PHPSESSID'])) {
                $result = $_COOKIE['PHPSESSID'];
            }
        }

        return $result;
    }

    public function getTemplateVariables($items, $source = 'email', $customer = NULL)
    {
        if (is_null($customer)) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
        }

        if ($source == 'alert') {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        } else {
            $firstItem = reset($items);

            $quote = Mage::getModel('sales/quote')
                ->setStoreId($firstItem->getStoreId())
                ->loadByCustomer($customer->getId());
        }

        $emailTemplateVariables['customer'] = $customer;
        if ($customer && $customer->getId()) {
            $emailTemplateVariables['customer_name'] = $customer->getFirstname() . ' ' . $customer->getLastname();
        } else {
            $emailTemplateVariables['customer_name'] = 'Guest';
        }

        $emailTemplateVariables['checkout_link'] = Mage::getUrl('checkout/cart');
        $emailTemplateVariables['store_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

        $reminderTime = $leftTime = Mage::helper('cartreservation')->getReminderTime($source);
        $ids_with_false = $ids = array();
        foreach ($items as $item) {
            $time = $item->leftReservationTime();
            if (($time < $leftTime) && ($time !== false)) {
                $leftTime = $time;
            }

            // 0 or False
            $time = $item->leftReminderTime($source);
            if ($time === false) {
                $ids_with_false[] = $item->getQuoteItemId();
            } elseif ($time <= $reminderTime) {
                if ($time < $reminderTime) {
                    $ids = array();
                }

                $ids[] = $item->getQuoteItemId();
                $reminderTime = $time;
            }
        }

        $ids = array_merge($ids, $ids_with_false);

        $customItems = $quote->getAllVisibleItems();
        foreach ($customItems as $item) {
            $item->setExpireReminderTime(in_array($item->getId(), $ids));
        }

        $layout = Mage::app()->getLayout();
        if ($source == 'email') {
            $defaultStoreId = Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStoreId();
            Mage::getDesign()->setPackageName(Mage::getStoreConfig('design/package/name', $defaultStoreId));
        }

        $totalsBlock = $layout->createBlock('checkout/cart_totals');

        if ($source == 'alert') {
            $totalsBlock->setTemplate('checkout/cart/totals.phtml');
        } else {
            $totalsBlock->setCustomQuote($quote)->setTemplate('cartreservation/email/totals.phtml');
        }

        $emailTemplateVariables['product_list'] = $layout->createBlock('checkout/cart')
            ->setTemplate('cartreservation/' . $source . '/cart.phtml')
            ->addItemRender('default', 'checkout/cart_item_renderer', 'cartreservation/' . $source . '/item.phtml')
            ->addItemRender('simple', 'checkout/cart_item_renderer', 'cartreservation/' . $source . '/item.phtml')
            ->addItemRender('grouped', 'checkout/cart_item_renderer_grouped', 'cartreservation/' . $source . '/item.phtml')
            ->addItemRender('configurable', 'checkout/cart_item_renderer_configurable', 'cartreservation/' . $source . '/item.phtml')
            ->setCustomItems($customItems)
            ->setChild('totals', $totalsBlock)
            ->setChild(
                'timer',
                $layout->createBlock('cartreservation/cart')->setTemplate('cartreservation/cart.phtml')
            )
            ->setSource($source)
            ->toHtml();

        $days = (int)($leftTime / 86400);
        $leftTime = $leftTime - $days * 86400;
        $hours = (int)($leftTime / 3600);
        $leftTime = $leftTime - $hours * 3600;
        $minutes = (int)($leftTime / 60);
        $leftTime = $leftTime - $minutes * 60;
        $seconds = $leftTime;

        $emailTemplateVariables['expire_time'] = sprintf(
            '%s %s:%s:%s hrs',
            ($days)? $days . ' days': '',
            str_pad($hours, 2, '0', STR_PAD_LEFT),
            str_pad($minutes, 2, '0', STR_PAD_LEFT),
            str_pad($seconds, 2, '0', STR_PAD_LEFT)
        );
        return $emailTemplateVariables;
    }
}
