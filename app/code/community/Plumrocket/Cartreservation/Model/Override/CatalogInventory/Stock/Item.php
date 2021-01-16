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

class Plumrocket_Cartreservation_Model_Override_CatalogInventory_Stock_Item extends Mage_CatalogInventory_Model_Stock_Item
{
    private static $_byProducts = array();

    public function resetByProductId($pid)
    {
        if (isset(self::$_byProducts[ $pid ])) {
            unset(self::$_byProducts[$pid]);
        }
        
        Mage::helper('cartreservation/product')->clearCache($pid);
    }
    
    public function getQty()
    {
        $qty = parent::getQty();
        if (Mage::helper('cartreservation')->moduleEnabled()
            && (! Mage::app()->getStore()->isAdmin()
                || Mage::helper('cartreservation')->isAdminMode()
            )
            && ! Mage::helper('cartreservation')->isOriginalMode()
        ) {
            $productId = $this->getProductId();

            if (!isset(self::$_byProducts[ $productId ])) {
                $this->_loadReservedCount($productId, $qty);
            }

            $key = Mage::helper('cartreservation')->isOnCart() ? 'corrected': 'source';
            $qty = self::$_byProducts[ $productId ][$key];
        }

        return $qty;
    }

    private function _loadReservedCount($productId, $qty)
    {
        $rc = $reservedQty = Mage::helper('cartreservation/product')->getReservedCount($productId, $qty);
        self::$_byProducts[ $productId ] = array(
            'source'    => $this->_getQtyForReservedCount($qty, $reservedQty),
            'corrected'    => $this->_getQtyForReservedCount(
                $qty,
                $reservedQty - Mage::helper('cartreservation/customer')->getReservedCount($productId, $qty)
            ),
            'reserved'  => $rc,
        );
    }

    private function _getQtyForReservedCount($qty, $reservedQty)
    {
        if ($reservedQty > $qty) {
            $reservedQty = $qty;
        }

        // reserve items
        if ($reservedQty > 0) {
            $qty = $qty - $reservedQty;
        }
        
        if ($qty < 0) {
            $qty = 0;
        }

        return $qty;
    }

    public function verifyStock($qty = null)
    {
        if (! Mage::helper('cartreservation')->moduleEnabled()) {
            return parent::verifyStock($qty);
        }

        $prevStatus = Mage::helper('cartreservation')->isOriginalMode();
        Mage::helper('cartreservation')->startOriginalMode();
        $res = parent::verifyStock($qty);
        // if before was original mode then should not cancel him.
        if (! $prevStatus) {
            Mage::helper('cartreservation')->stopOriginalMode();
        }

        return $res;
    }

    public function getReservedQty()
    {
        if (! Mage::helper('cartreservation')->moduleEnabled()) {
            return 0;
        }

        $productId = $this->getProductId();

        if (!isset(self::$_byProducts[ $productId ])) {
            $this->getQty();
        }

        return array_key_exists($productId, self::$_byProducts)?
            self::$_byProducts[ $productId ]['reserved']: 0;
    }
    
    public function isReserved()
    {
        return Mage::helper('cartreservation')->moduleEnabled() && ($this->getReservedQty() > 0);
    }

    protected function _beforeSave()
    {
        if (! Mage::helper('cartreservation')->moduleEnabled()) {
            return parent::_beforeSave();
        }

        // _beforeSave function call another functon where will be called this 
        // function. And returned value must be system qtu, not with reserved.
        $prevStatus = Mage::helper('cartreservation')->isOriginalMode();
        Mage::helper('cartreservation')->startOriginalMode();
        $res = parent::_beforeSave();
        // if before was original mode then should not cancel him.
        if (! $prevStatus) {
            Mage::helper('cartreservation')->stopOriginalMode();
        }

        return $res;
    }

    protected function _afterSave()
    {
        $this->resetByProductId($this->getProductId());
        return parent::_afterSave();
    }

    // Service functions

    public function getOriginalQty()
    {
        return parent::getQty();
    }

    public function getCorrectedQty()
    {
        $productId = $this->getProductId();
        $qty = false;

        if (isset(self::$_byProducts[ $productId ])) {
            $qty = self::$_byProducts[ $productId ]['corrected'];
        }

        return $qty;
    }

    public function getSourceQty()
    {
        $productId = $this->getProductId();
        $qty = false;

        if (isset(self::$_byProducts[ $productId ])) {
            $qty = self::$_byProducts[ $productId ]['source'];
        }

        return $qty;
    }
}
