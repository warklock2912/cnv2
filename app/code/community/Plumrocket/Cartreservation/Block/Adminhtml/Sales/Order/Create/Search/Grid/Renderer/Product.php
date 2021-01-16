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

class Plumrocket_Cartreservation_Block_Adminhtml_Sales_Order_Create_Search_Grid_Renderer_Product extends Mage_Adminhtml_Block_Sales_Order_Create_Search_Grid_Renderer_Product
{
    /**
     * Render product name to add Configure link
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $rendered =  parent::render($row);
        $crText = '';

        Mage::helper('cartreservation/product')->initAdmin($row);
        if (! $row->getIsSalable() && $row->getIsReserved()) {
            $crText = '<span class="f-right" style="color: #d20000; margin: 0px 15px;">Reserved</span>';
        }

        return $rendered . $crText;
    }    
}