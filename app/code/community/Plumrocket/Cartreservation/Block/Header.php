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

class Plumrocket_Cartreservation_Block_Header extends Mage_Core_Block_Template
{
    protected function _toHtml()
    {
        if (! Mage::helper('cartreservation')->moduleEnabled()) {
            $this->setTemplate('cartreservation/empty.phtml');
        }

        return parent::_toHtml();
    }
    
    public function getFormat()
    {
        return Mage::helper('cartreservation')->__(
            Mage::getStoreConfig('cartreservation/format/format')
        );
    }

    public function getProductFormat()
    {
        return Mage::helper('cartreservation')->__(
            Mage::getStoreConfig('cartreservation/format/product_timer_format')
        );
    }
    
    public function needReloadPage()
    {
        return ((int)Mage::getStoreConfig('cartreservation/cart/after_end') == Plumrocket_Cartreservation_Model_Values_Keepincart::REMOVE)
            && (! Mage::helper('cartreservation')->isReservedForever());
    }
    
    public function getExpiryText()
    {
        $text = (Mage::getStoreConfig('cartreservation/cart/type') == Plumrocket_Cartreservation_Model_Values_Types::RESERVE_ITEM) ? 
            'This item is no longer reserved.' :
            'The cart is no longer reserved.';
        return Mage::helper('cartreservation')->__($text);
    }

    public function leftReminderTime()
    {
        if (Mage::getStoreConfig('cartreservation/reminders_alert/show')
            && (! Mage::helper('cartreservation')->isReservedForever())
            //&& Mage::helper('cartreservation/customer')->hasItems()
        ) {
            $time = Mage::helper('cartreservation/customer')->leftReminderTime('alert');

            if ($time === 0) {
                Mage::helper('cartreservation/customer')->expireItemsWithExpiredReminderTime('alert');
                $time = Mage::helper('cartreservation/customer')->leftReminderTime('alert');
            }

            if ($time === false) {
                $time = 'forever';
            }
        } else {
            $time = 'forever';
        }

        return $time;
    }
}