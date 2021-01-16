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

class Plumrocket_Cartreservation_Model_ObserverEncoded extends Mage_Core_Model_Abstract
{
    // Before all controller's actions
    public function removeLostReserveItemsForCustomer(Varien_Event_Observer $observer)
    {
        if (Mage::helper('cartreservation')->moduleSessionEnabled()
            && (Mage::getSingleton('plumbase/observer')->customer() == Mage::getSingleton('plumbase/product')->currentCustomer())
        ) {
            $items = Mage::helper('cartreservation/customer')->getItems();
            $onFrontend = ! Mage::app()->getStore()->isAdmin();
            
            // Check on frontend. Do not check on adminhtml.
            if ($onFrontend && !$this->_chechkRequestPathParams($observer)) {
                return false;
            }
            
            $didDeleted = false;
            // delete no reserved
            foreach ($items as $item) {
                if ((int)Mage::getStoreConfig('cartreservation/cart/after_end') == Plumrocket_Cartreservation_Model_Values_Keepincart::REMOVE
                    && !$item->isReserved()
                ) {
                    // Log:
                        $deletedData = $item->getData();
                    $item->remove();
                    // Log:
                        Mage::helper('cartreservation')->logAction('delete_timeout', $deletedData);

                    $didDeleted = true;
                } elseif ($onFrontend) {
                    // Call on frontend. Do not call on adminhtml
                    if (Mage::helper('cartreservation')->isOnCheckout()) {
                        $item->setCheckoutMode();
                    } else {
                        $item->cancelCheckoutMode();
                    }
                }
            }
            
            if ($didDeleted && $onFrontend) {
                $quote = Mage::helper('cartreservation')->getQuote();
                $quote->collectTotals();
                $quote->save();
                Mage::getSingleton('checkout/session')->setQuoteId($quote->getId());
            }

            Mage::helper('cartreservation/customer')->resetItems();
        }

        return $observer;
    }

    protected function _chechkRequestPathParams($observer)
    {
        $controller = $observer->getEvent()->getControllerAction();
        $request = $controller->getRequest();
        
        /**
         * Important!
         * Ignore actions if this is 404 page.
         */
        if ($request->getModuleName() == 'cms'
            && $request->getControllerName() == 'index'
            && $request->getActionName() == 'noRoute'
        ) {
            return false;
        }
        
        $event = new Varien_Object();
        $event->setOnCheckout(
            $request->getModuleName() == 'checkout'
            && $request->getControllerName() == 'onepage'
            || $controller instanceof Mage_Checkout_Controller_Action
        );
        $event->setOnCart(
            $request->getModuleName() == 'checkout'
            || $controller instanceof Mage_Checkout_CartController
            || $controller instanceof Mage_Checkout_Controller_Action
        );

        $this->_checkOnCheckout($event, $request);
        Mage::dispatchEvent(
            'cartreservation_check_on_checkout',
            array('object' => $event, 'request' => $request)
        );

        Mage::helper('cartreservation')->registerOnCheckout(
            $event->getOnCheckout()
        );
        Mage::helper('cartreservation')->registerOnCart(
            $event->getOnCart()
        );
        return true;
    }


    protected function _checkOnCheckout($event,$request)
    {
        $onCheckout = $event->getOnCheckout();
        $onCart = $event->getOnCart();

        if (!$onCheckout) {
            $onCheckout = ($request->getModuleName() == 'firecheckout' && $request->getControllerName() == 'index');

            $event->setOnCheckout($onCheckout);
        }

        if (!$onCheckout) {
            $onCheckout = ($request->getModuleName() == 'anattadesign_awesomecheckout' && $request->getControllerName() == 'onepage');

            $event->setOnCheckout($onCheckout);
        }

        if (!$onCheckout) {
            $onCheckout = ($request->getModuleName() == 'opcheckout' && $request->getControllerName() == 'onepage');
            $event->setOnCheckout($onCheckout);
        }

        if (!$onCheckout) {
            $onCheckout = ($request->getModuleName() == 'paypal' || $request->getModuleName() == 'onestepcheckout');
            $event->setOnCheckout($onCheckout);
        }

        if (!$onCart) {
            $onCart = (($request->getModuleName() == 'ajaxcart' && $request->getControllerName() == 'checkout_cart')
                || $onCheckout);

            $event->setOnCart($onCart);
        }


        return $this;

    }


    public function beforeOrderSave(Varien_Event_Observer $observer)
    {
        if (Mage::helper('cartreservation')->moduleSessionEnabled()) {
            Mage::helper('cartreservation')->startOriginalMode();
        }

        return $observer;
    }

    public function orderSave(Varien_Event_Observer $observer)
    {
        if (Mage::helper('cartreservation')->moduleSessionEnabled()) {
            Mage::helper('cartreservation')->stopOriginalMode();

            if ($observer->getEvent()->getOrder()->getState() != Mage_Sales_Model_Order::STATE_CANCELED) {
                $quoteId = $observer->getEvent()->getQuote()->getId();
                if ($quoteId) {
                    $_exists_items = Mage::getModel('cartreservation/item')->getCollection()
                        ->addFieldToFilter('quote_id', $quoteId);
                            
                    foreach ($_exists_items as $item) {
                        // Log:
                            $deletedData = $item->getData();
                        $item->delete();
                        // Log:
                            Mage::helper('cartreservation')->logAction('delete_order', $deletedData);
                    }
                }
            }
        }

        return $observer;
    }
    
    /*
	 * Merge function for all actions.
	 */
    public function mergeCart(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getCart()->getQuote();
        $this->mergeCartWithQuote($quote);
        return $observer;
    }

    public function mergeCartWithQuote($quote)
    {
        if (Mage::helper('cartreservation')->moduleSessionEnabled()
            && ((Mage::getStoreConfig('cartreservation/general/type') == Plumrocket_Cartreservation_Model_Values_Usertypes::ALL_USERS)
                || Mage::helper('cartreservation/customer')->getId()
            )
        ) {
            $quoteId = $quote->getId();
            
            // Get exists ids
            $tmp_items = Mage::getModel('cartreservation/item')->getCollection()
                ->addFieldToFilter('quote_id', $quoteId);
                
            $crItems = array();
            foreach ($tmp_items as $item) {
                $crItems[ $item->getData('quote_item_id') ] = $item;
            }

            unset($tmp_items);
            
            // Get newest ids
            $tmp_items = $quote->getAllItems();
            
            $systemItems = array();
            foreach ($tmp_items as $item) {
                // if it is not child product
                if (! $item->getData('parent_item_id')) {
                    $systemItems[ $item->getId() ] = $item;
                }
            }

            foreach ($tmp_items as $item) {
                $pid = $item->getData('parent_item_id');
                if ($pid && isset($systemItems[$pid])) {
                    //$systemItems[$pid]->setData('child_quote_item_id', $item->getId());
                    $systemItems[$pid] = $this->_appendToAttr($systemItems[$pid], 'child_quote_item_id', $item->getId());
                    //$systemItems[$pid]->setData('child_product_id', $item->getProductId());
                    $systemItems[$pid] = $this->_appendToAttr($systemItems[$pid], 'child_product_id', $item->getProductId());
                }
            }

            unset($tmp_items);

            // process imported items
            foreach ($systemItems as $id => $systemItem) {
                $systemData = $systemItem->getData();
                // item earlier has cr record under guest
                if (array_key_exists('old_reserve_item', $systemData)) {
                    // Already exists cr item for this product.
                    // Replace it by guest record as newest.
                    if (array_key_exists($id, $crItems)) {
                        // Log:
                            $deletedData = $crItems[$id]->getData();
                        $crItems[$id]->delete();
                        unset($crItems[$id]);
                        // Log:
                            Mage::helper('cartreservation')->logAction('delete_import', $deletedData);
                    }

                    $oldCrItem = $systemData['old_reserve_item'];
                    $oldQty = $oldCrItem->getQty();
                    $allowedQty = $this->_calculateQty($systemData, $oldQty);

                    $systemData = $this->_filterChildrenForBundle($systemData);

                    $oldCrItem->setData(
                        array_merge(
                            $oldCrItem->getData(), array(
                            'quote_id'                => $quoteId,
                            'quote_item_id'            => $id,
                            'customer_id'            => Mage::helper('cartreservation/customer')->getId(),
                            'qty'                    => $allowedQty,
                            'child_quote_item_id'    => isset($systemData['child_quote_item_id']) ? $systemData['child_quote_item_id'] : array(),
                            'child_product_id'        => isset($systemData['child_product_id'])? $systemData['child_product_id'] : array(),
                            'session_id'            => Mage::helper('cartreservation/customer')->getSessionId(),
                            )
                        )
                    )->save();

                    // Log:
                        Mage::helper('cartreservation')->logAction('change_import', $oldCrItem->getData(), $oldQty);

                    unset($systemItems[$id]);
                }
            }
            
            // Match ids
            $mergeArray = array(
                'added'        => array(),
                'changed'    => array(),
                'deleted'    => array()
            );
            
            foreach ($systemItems as $id => $newItem) {
                if (! isset($crItems[ $id ])) {
                    $mergeArray['added'][] = $id;
                } elseif ($newItem->getQty() != $crItems[ $id ]->getQty()) {
                    $mergeArray['changed'][] = $id;
                }
            }

            foreach ($crItems as $id => $_) {
                if (! isset($systemItems[ $id ])) {
                    $mergeArray['deleted'][] = $id;
                }
            }

            // Merge!
            $createdTime = Mage::getModel('core/date')->timestamp(time());
            foreach ($mergeArray['added'] as $id) {
                $newData = $systemItems[$id]->getData();
                $allowedQty = $this->_calculateQty($newData, 0);

                $newData = $this->_filterChildrenForBundle($newData);

                if ($newData['qty'] == $allowedQty) {
                    $newItem = Mage::getModel('cartreservation/item')->setData(
                        array(
                        'product_id'            => $newData['product_id'],
                        'quote_id'                => $quoteId,
                        'quote_item_id'            => $id,
                        'customer_id'            => Mage::helper('cartreservation/customer')->getId(),
                        'child_quote_item_id'    => isset($newData['child_quote_item_id'])? $newData['child_quote_item_id'] : array(),
                        'child_product_id'        => isset($newData['child_product_id'])? $newData['child_product_id'] : array(),
                        'session_id'            => Mage::helper('cartreservation/customer')->getSessionId(),
                        'qty'                    => $newData['qty'],
                        'cart_date'                => Mage::helper('cartreservation')->isOnCheckout()? 0: $createdTime,
                        'cart_time'                => 0,
                        'checkout_date'            => Mage::helper('cartreservation')->isOnCheckout()? $createdTime: 0, // if created on checkout
                        'checkout_time'            => 0,
                        'store_id'                => Mage::app()->getStore()->getId(),
                        )
                    );

                    $newItem->save();
                    // Log:
                        Mage::helper('cartreservation')->logAction('add', $newItem->getData());
                }
            }

            foreach ($mergeArray['changed'] as $id) {
                $item = $crItems[$id];
                $oldQty = $item->getQty();
                $allowedQty = $this->_calculateQty($systemItems[$id]->getData(), $oldQty);
                if ($allowedQty != $oldQty) {
                    $item->setQty($allowedQty)
                        ->save();
                    // Log:
                        Mage::helper('cartreservation')->logAction('change', $item->getData(), $oldQty);
                }
            }
            
            if ($mergeArray['added'] || $mergeArray['changed']) {
                $sql = '';
                
                if (Mage::getStoreConfig('cartreservation/cart/type') == Plumrocket_Cartreservation_Model_Values_Types::RESERVE_CART) {
                    Mage::helper('cartreservation/customer')->resetItems();
                
                    $leftTime = -1;
                    $cartDate = Mage::getModel('core/date')->timestamp(time());
                    $cartTime = 0;
                    
                    foreach (Mage::helper('cartreservation/customer')->getItems() as $item) {
                        $itemTime = (int)$item->leftReservationTime();
                        if ($itemTime > $leftTime) {
                            $leftTime = $itemTime;
                            
                            $cartDate = $item->getCartDate();
                            $cartTime = $item->getCartTime();
                        }
                    }

                    $sqlResetCheckout = '';
                    // Reset checkout timers only if customer not on checkout
                    if (!Mage::helper('cartreservation')->isOnCheckout()) {
                        $sqlResetCheckout = '`checkout_date` = 0, 
							`checkout_time` = 0, ';
                    }
                    
                    $sql = sprintf(
                        "
						UPDATE `%s` SET 
							`cart_date` = '%u', 
							`cart_time` = '%u', 
							{$sqlResetCheckout} 
							`alerted` = 0, 
							`emailed` = 0 
						WHERE `quote_id` = %u",
                        Mage::getSingleton('core/resource')->getTableName('cartreservation_item'),
                        $cartDate,
                        $cartTime,
                        $quoteId
                    );
                } elseif ($mergeArray['added']) {
                    // Reset checkout timers only if customer not on checkout
                    if (! Mage::helper('cartreservation')->isOnCheckout()) {
                        $sql = sprintf(
                            "
							UPDATE `%s` SET 
								`checkout_date` = 0, 
								`checkout_time` = 0
							WHERE `quote_id` = %u",
                            Mage::getSingleton('core/resource')->getTableName('cartreservation_item'),
                            $quoteId
                        );
                    }
                }
                
                if ($sql) {
                    $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
                    $connection->query($sql);
                }
            }
            
            foreach ($mergeArray['deleted'] as $id) {
                // Log:
                    $deletedData = $crItems[$id]->getData();
                $crItems[$id]->delete();
                // Log:
                    Mage::helper('cartreservation')->logAction('delete', $deletedData);
            }

            Mage::helper('cartreservation/customer')->resetItems();
        }
    }

    protected function _appendToAttr($obj, $attr, $item)
    {
        $val = $obj->getData($attr);
        $val[] = $item;
        $obj->setData($attr, $val);
        return $obj;
    }

    // Check if new qty > old qty, then if diff beetwen it > free qty then 
    // set new qty as old qty + free qty
    // Free qty: 2
    // Old qty: 4 (calculated with old cr items)
    // New qty: 7
    // Diff = 7 - 4 = 3, 3 > free count 2 because New qty = 4 + 2 = 6.
    private function _calculateQty($data, $oldReservedQty)
    {
        $qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($data['product_id'])->getQty();
        $pids = isset($data['child_product_id']) ? $data['child_product_id'] : array();
        if ($pids) {
            $etQty = $qty = 999999;
            // foreach for bundle products
            foreach ($pids as $pid) {
                // get qty without correction
                $_qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($pid)
                    ->getQty();
                if ($_qty && ($_qty < $qty)) {
                    $qty = $_qty;
                }
            }

            if ($qty == $etQty) {
                $qty = 0;
            }
        }

        $newReservedQty = $data['qty'];

        $diff = $newReservedQty - $oldReservedQty;
        // if old > new then diff < 0 then always will be < qty
        if ($diff > $qty) {
            $newReservedQty = $oldReservedQty + $qty;
        }

        return $newReservedQty;
    }

    protected function _filterChildrenForBundle($data)
    {
        // check if has children and bundle with children count > 1
        if (isset($data['child_product_id']) && (count($data['child_product_id']) > 1)) {
            $pids = array();
            $qids = array();

            foreach ($data['child_product_id'] as $i => $pid) {
                $product = Mage::getModel('catalog/product')->load($pid);
                if (Mage::helper('cartreservation/product')->getManageStock($product)) {
                    $pids[] = $pid;
                    $qids[] = $data['child_quote_item_id'][$i];
                }
            }

            $data['child_product_id'] = $pids;
            $data['child_quote_item_id'] = $qids;
        }

        return $data;
    }

    public function productsAreSalable(Varien_Event_Observer $observer)
    {
        if (Mage::helper('cartreservation')->moduleEnabled()) {
            $products = $observer->getEvent()->getCollection();
            
            foreach ($products as $product) {
                Mage::helper('cartreservation/product')->init($product);
            }
        }

        return $observer;
    }
    
    public function productIsSalable(Varien_Event_Observer $observer)
    {
        if (Mage::helper('cartreservation')->moduleEnabled()) {
            $product = $observer->getEvent()->getProduct();
            Mage::helper('cartreservation/product')->init($product);
        }

        return $observer;
    }
}
