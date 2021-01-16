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

class Plumrocket_Cartreservation_Block_Cart extends Mage_Checkout_Block_Cart
{
    protected function _toHtml()
    {
        if (! Mage::helper('cartreservation')->moduleEnabled()) {
            $this->setTemplate('cartreservation/empty.phtml');
        }

        return parent::_toHtml();
    }

    public function modeReserveCart()
    {
        return Mage::helper('cartreservation')->getShowModule()
            && Mage::helper('cartreservation')->modeReserveCart();
    }
    
    public function getTime()
    {
        if (Mage::helper('cartreservation')->isReservedForever()) {
            $time = 'forever';
        } elseif (Mage::helper('cartreservation/customer')->hasItems()) {
            $time = (int)Mage::helper('cartreservation/customer')->leftReservationTime();
        } else {
            $time = 'no';
        }
        
        return $time;
    }
    
    public function isVisible()
    {
        // Show if not on Checkout page or there shold be show timer
        return (! Mage::helper('cartreservation')->isOnCheckout())
            || Mage::getStoreConfig('cartreservation/checkout/timer_display') == Plumrocket_Cartreservation_Model_Values_Timerdisplay::SHOW;
    }
}
