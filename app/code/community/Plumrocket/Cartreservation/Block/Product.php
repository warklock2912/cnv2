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

class Plumrocket_Cartreservation_Block_Product extends Mage_Core_Block_Template
{
    protected $_template = 'cartreservation/product.phtml';
    protected $_time = null;
    protected $_product = null;
    protected $_shouldShow = false;

    protected function _toHtml()
    {
        if (! (Mage::helper('cartreservation')->moduleEnabled()
            && (Mage::getStoreConfig('cartreservation/format/product_timer_show') || $this->_shouldShow)
            && ($this->getTime() > 0)
        )) {
            $this->setTemplate('cartreservation/empty.phtml');
        }

        return parent::_toHtml();
    }

    public function getTime()
    {
        if (is_null($this->_time)) {
            $this->_time = 0;

            $product = $this->getProduct();
            if ($product
                && $product->getId()
                && ! $product->getIsSalable()
                && $product->getIsReserved()
            ) {
                $this->_time = Mage::helper('cartreservation/product')->leftReservationTime($product->getId());
            }
        }

        return $this->_time;
    }

    public function setProduct($product)
    {
        $this->_product = $product;
        return $this;
    }

    public function getProduct()
    {
        if (is_null($this->_product)) {
            $this->_product =  Mage::registry('product');
            if(!$this->_product && $this->getProductId()){
                $this->_product = Mage::getModel('catalog/product')->load($this->getProductId());
            }
        }

        return $this->_product;
    }

    public function shouldShow()
    {
        $this->_shouldShow = true;
        return $this;
    }

    public function shouldNotShow()
    {
        $this->_shouldShow = false;
        return $this;
    }
}
