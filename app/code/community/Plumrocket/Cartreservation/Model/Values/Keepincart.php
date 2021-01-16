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

class Plumrocket_Cartreservation_Model_Values_Keepincart
{
    const REMOVE = 1;
    const KEEP = 2;
    
    public function toOptionArray()
    {
        return array(
            array('value' => self::REMOVE, 'label' => Mage::helper('cartreservation')->__('Remove product(s) from cart')),
            array('value' => self::KEEP, 'label' => Mage::helper('cartreservation')->__('Keep product(s) in cart')),
        );
    }
}