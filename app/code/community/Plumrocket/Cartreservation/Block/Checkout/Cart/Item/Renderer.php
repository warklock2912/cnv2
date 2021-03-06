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

class Plumrocket_Cartreservation_Block_Checkout_Cart_Item_Renderer 
    extends Mage_Checkout_Block_Cart_Item_Renderer
{
    /**
     * Override this function
     */
    public function getOptionList()
    {
        $options = $this->getProductOptions();
        
        if ($this->modeReserveItem()) {
            $time = Mage::helper('cartreservation')->getItemTime($this->getItem());
            if ($time != 'no') {
                array_unshift(
                    $options, array(
                    'label' => '||reserved::' . $time . '||',
                    'value' => '',
                    'print_value' => '',
                    'option_id' => 0,
                    'option_type' => 'drop_down'
                    )
                );
            }
        }

        return $options;
    }
    
    public function modeReserveItem()
    {
        // moduleEnabled will be called inside the function
        return Mage::helper('cartreservation')->getShowModule()
            && ! Mage::helper('cartreservation')->modeReserveCart();
    }
}
