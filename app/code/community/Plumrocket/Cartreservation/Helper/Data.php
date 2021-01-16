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

class Plumrocket_Cartreservation_Helper_Data extends Plumrocket_Cartreservation_Helper_Main
{
    protected $_originalQtyMode = false;
    protected $_allowAdminQty = false;

    protected static $_onCart = false;
    protected static $_onCheckout = false;

    public function moduleEnabled($store = null, $disableSessionCheck = true)
    {
        $enable = (bool)Mage::getStoreConfig('cartreservation/general/enable', $store)
            && ($disableSessionCheck
                || Mage::helper('cartreservation/customer')->getSessionId()
                || Mage::helper('cartreservation/customer')->getId()
            );

        if ($enable) {
            // If the module is enabled then check access by stores id.
            $enable = false;
            $currentStoreId = Mage::app()->getStore()->getStoreId();

            // For Cron actions
            if ($currentStoreId == 0) {
                $enable = true;
            } else {
                $stores = explode(',', (string)Mage::getStoreConfig('cartreservation/general/visibility', $store));
                
                foreach ($stores as $id) {
                    $storeId = (int)$id;
                    // store id = 0 if the option "All stores" is selected.
                    if (($storeId == 0) || ($storeId == $currentStoreId)) {
                        $enable = true;
                        break;
                    }
                }
            }
        }

        return $enable;
    }

    public function moduleSessionEnabled($store = null)
    {
        return $this->moduleEnabled($store, false);
    }
    
    /*
	Check if cart with this id reserved
	 */
    public function checkIfCartReserved($quoteId)
    {
        $items = Mage::getModel('cartreservation/item')->getCollection()
            ->addFieldToFilter('quote_id', $quoteId);
                
        foreach ($items as $item) {
            // Warning: Do not delete the param FALSE, else we shall get recursion.
            // False: check just item's reserved, not check carts these is them parents.
            if ($item->isReserved(false)) {
                return true;
            }
        }

        return false;
    }

    public function getItemTime($item = false)
    {
        $newItem = false;
        if ($item) {
            $newItem = Mage::getModel('cartreservation/item')->load($item->getId(), 'quote_item_id');
        }

        $time = 'no';  
        if ($newItem && $newItem->getId() > 0) {
            if (! Mage::helper('cartreservation')->isReservedForever()) {
                $time = (int)$newItem->leftReservationTime();
            } else {
                $time = 'forever';
            }
        }

        return $time;
    }

    public function isReservedForever()
    {
        $where = $this->isOnCheckout()? 'checkout': 'cart';
        return ($this->getConfigTime($where) === 0);
    }
    
    public function registerOnCheckout($bool)
    {
        self::$_onCheckout = $bool;
    }
    
    public function isOnCheckout()
    {
        return self::$_onCheckout;
    }

    public function registerOnCart($bool)
    {
        self::$_onCart = $bool;
    }
    
    public function isOnCart()
    {
        return self::$_onCart;
    }
    
    public function modeReserveCart()
    {
        return Mage::getStoreConfig('cartreservation/cart/type') == Plumrocket_Cartreservation_Model_Values_Types::RESERVE_CART
            || $this->isReservedForever()
            || $this->isOnCheckout();
    }
    
    public function getConfigTime($source = 'cart')
    {
        if (! in_array($source, array('checkout', 'cart'))) {
            $source = 'cart';
        }
        
        $time = Mage::getStoreConfig('cartreservation/' . $source . '/time');
        $timesArr = explode(',', $time);
        $time = (int)$timesArr[0] * 86400 + (int)$timesArr[1] * 3600 + (int)$timesArr[2] * 60 + (int)$timesArr[3];
        
        return $time;
    }
    
    public function getReminderTime($source = 'email')
    {
        if (! in_array($source, array('email', 'alert'))) {
            $source = 'email';
        }
        
        $time = Mage::getStoreConfig('cartreservation/reminders_' . $source . '/time');
        $timesArr = explode(',', $time);
        $time = (int)$timesArr[0] * 86400 + (int)$timesArr[1] * 3600 + (int)$timesArr[2] * 60 + (int)$timesArr[3];
        
        return $time;
    }

    public function disableExtension()
    {
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');
        $connection->delete($resource->getTableName('core/config_data'), array($connection->quoteInto('path IN (?)', array('cartreservation/general/enable', 'cartreservation/general/visibility', 'cartreservation/cart/time', 'cartreservation/checkout/time', 'cartreservation/reminders_alert/time', 'cartreservation/reminders_alert/template', 'cartreservation/format/format', 'reservation_after_checkout/general/enable', 'reservation_after_checkout/general/time',))));
        $config = Mage::getConfig();
        $config->reinit();
        Mage::app()->reinitStores();
    }
    
    public function getShowModule()
    {
        return $this->moduleEnabled()
            && (Mage::getStoreConfig('cartreservation/general/type') == Plumrocket_Cartreservation_Model_Values_Usertypes::ALL_USERS
                || (Mage::helper('cartreservation/customer')->getId() > 0)
            );
    }
    
    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    // Log:
    public function logAction($action, $data, $oldQty = 0)
    {
        if ((int)Mage::getConfig()->getNode('default/cartreservation/log/enable')) {
            $qtyObj = Mage::getModel('cataloginventory/stock_item')->loadByProduct($data['product_id']);
            $qtyObj->getQty();

            $logItem = Mage::getModel('cartreservation/log')->setData(
                array(
                'action'        => $action,
                'quote_id'        => array_key_exists('quote_id', $data)? $data['quote_id']: 0,
                'product_id'    => array_key_exists('product_id', $data)? $data['product_id']: 0,
                'customer_id'    => array_key_exists('customer_id', $data)? $data['customer_id']: 0,
                'product_qty'    => $qtyObj->getOriginalQty(),
                'old_qty'        => $oldQty,
                'qty'            => array_key_exists('qty', $data)? $data['qty']: 0,
                'cr_qty'        => $qtyObj->getSourceQty(),
                'cr_corrected'    => $qtyObj->getCorrectedQty(),
                'referer'        => isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER']: '',
                'date_created'    => strftime('%F %T', Mage::getModel('core/date')->timestamp(time())),
                'sid'            => Mage::helper('cartreservation/customer')->getSessionId()
                )
            )->save();
        }
    }

    public function startOriginalMode() 
    {
        $this->_originalQtyMode = true;
    }

    public function stopOriginalMode() 
    {
        $this->_originalQtyMode = false;
    }

    public function isOriginalMode() 
    {
        return $this->_originalQtyMode;
    }

    public function startAdminMode() 
    {
        $this->_allowAdminQty = true;
    }

    public function stopAdminMode() 
    {
        $this->_allowAdminQty = false;
    }

    public function isAdminMode() 
    {
        return $this->_allowAdminQty;
    }

    // adminhtml code
    public function checkAndMergeCart($quote)
    {
        if ($customerId = $quote->getCustomerId()) {
            Mage::helper('cartreservation/customer')->setCustomId($customerId);
            $observer = new Varien_Event_Observer();
            Mage::getSingleton('cartreservation/observerEncoded')->removeLostReserveItemsForCustomer($observer);

            if ($quote->getId()) {
                Mage::getSingleton('cartreservation/observerEncoded')->mergeCartWithQuote($quote);
            }
        }
    }

    // @deprecated method
    public function getReservedProductItems($pid, $limit = 0) 
    {
        return Mage::helper('cartreservation/product')->getReservedItems($pid, $limit = 0);
    }

    // @deprecated method
    public function getReservedCount($pid, $limit = 0) 
    {
        return Mage::helper('cartreservation/product')->getReservedCount($pid, $limit = 0);
    }

    // deprecated method
    public function clearCacheByProduct($pid) 
    {
        return Mage::helper('cartreservation/product')->clearCache($pid);
    }
}
     
