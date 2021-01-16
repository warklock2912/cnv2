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
 
class Plumrocket_Cartreservation_Model_Item extends Mage_Core_Model_Abstract
{
    protected $_allowedReservation = null;
    protected $_loaded = false;
    static protected $_cacheCrStatusForCategories = array();

    const INHERITED = 0;
    const DISABLED = 1;
    const ENABLED = 2;

    protected function _construct()
    {
        if (Mage::getSingleton('plumbase/observer')->customer() == Mage::getSingleton('plumbase/product')->currentCustomer()) {
            $this->_init('cartreservation/item');
        }
    }
    
    public function isReserved($checkIfCartReserved = true)
    {
        $left = $this->leftReservationTime();
        $result = ((int)$left > 0) || ($left === false);
        
        return $result || // else
            (
                // check if parent cart is reserved (if parent cart has reserved items)
                // this check called if CR reserved by cart - see second condition
                $checkIfCartReserved
                && (Mage::getStoreConfig('cartreservation/cart/type') == Plumrocket_Cartreservation_Model_Values_Types::RESERVE_CART)
                && Mage::helper('cartreservation')->checkIfCartReserved($this->getQuoteId())
            );        
    }
    
    public function setCheckoutMode()
    {
        $needSave = false;
        // keep Cart data
        if (! $this->isCheckoutMode()) {
            $data = $this->getData();

            // if the item still reserved on cart
            if ($this->isReserved()) {
                // get left time with start to end.
                $cartTime = Mage::getModel('core/date')->timestamp(time())- $data['cart_date'];
                if ($cartTime < 0) {
                    $cartTime = 0;
                }

                // -----
                $data['cart_time'] = $data['cart_time'] + $cartTime;
                $data['cart_date'] = 0;
                
                if (Mage::getStoreConfig('cartreservation/checkout/timer_behavior') == Plumrocket_Cartreservation_Model_Values_Timerbehavior::RESTART) {
                    $data['checkout_time'] = '0';
                }
            } else {
                $data['checkout_time'] = Mage::helper('cartreservation')->getConfigTime('checkout');
            }
            
            // switch to checkout mode
            $data['checkout_date'] = Mage::getModel('core/date')->timestamp(time());

            $this->setData($data);
            $this->save();
            // Log:
                Mage::helper('cartreservation')->logAction('on_checkout', $this->getData());
        }
    }
    
    public function cancelCheckoutMode()
    {
        // keep CR data
        if ($this->isCheckoutMode()) {
            $data = $this->getData();
            
            // get left time with start to end.
            $checkoutTime = Mage::getModel('core/date')->timestamp(time()) - $data['checkout_date'];
            if ($checkoutTime < 0) {
                $checkoutTime = 0;
            }

            // ----
            $data['checkout_time'] = $data['checkout_time'] + $checkoutTime;
            $data['checkout_date'] = 0;
            $data['cart_date'] = Mage::getModel('core/date')->timestamp(time());
            
            $this->setData($data);
            $this->save();

            // Log:
                Mage::helper('cartreservation')->logAction('left_checkout', $this->getData());
        }
    }
    
    /*
	 * Reset reservation time
	 */
    public function resetReservationTime($type = 'all')
    {
        switch ($type) {
            case 'cart':
                $this->setCartDate(Mage::getModel('core/date')->timestamp(time()));
                $this->setCartTime('0');
                break;
            case 'checkout':
                $this->setCheckoutDate(Mage::getModel('core/date')->timestamp(time()));
                $this->setCheckoutTime('0');
                break;
            case 'all':
                $this->setCartDate(Mage::getModel('core/date')->timestamp(time()));
                $this->setCartTime('0');
                $this->setCheckoutDate(Mage::getModel('core/date')->timestamp(time()));
                $this->setCheckoutTime('0');
                break;
        }

        $this->save();
    }
    
    public function remove()
    {
        $ids = (array)$this->getChildQuoteItemId(); // return array of id
        $ids[] = $this->getQuoteItemId();
        
        $items = Mage::getModel('cartreservation/alias')->getCollection()
            ->addFieldToFilter('item_id', array('in' => $ids));

        foreach ($items as $item) {
            Mage::helper('cartreservation/product')->cleanCache($item->getProductId());
            $item->delete();
        }

        /* 
			Prevent delete item if linked cart items were not deleted
		*
		$count = Mage::getModel('cartreservation/alias')->getCollection()
			->addFieldToFilter('item_id', array('in' => $ids))
			->count();
		if ($count) {
			return;
		}
		*/
            
        $this->delete();
    }

    public function leftReservationTime()
    {
        if ($this->isCheckoutMode()) {
            $where = 'checkout';
            $time = $this->getCheckoutDate();
            $totalTime = $this->getCheckoutTime();
        } else {
            $where = 'cart';
            $time = $this->getCartDate();
            $totalTime = $this->getCartTime();
        }
        
        $offset = Mage::helper('cartreservation')->getConfigTime($where);
        // return FALSE if this is forever reserved item
        if ($offset == 0) {
            return false;
        }

        //    start reserve         current time            + cart_time          reserved time
        // ---------|---------------------|-----------------------|------------------|
        //
        $left = ($time + $offset) - Mage::getModel('core/date')->timestamp(time()) - $totalTime;
        return ($left > 0)? $left: 0;
    }
    
    private function isCheckoutMode()
    {
        return ($this->getCartDate() == 0) && ($this->getCheckoutDate() > 0);
    }

    public function leftReminderTime($source = 'email')
    {
        $leftTime = false;
        $leftReservation = $this->leftReservationTime();

        if (($leftReservation !== false) && ! $this->getData($source . 'ed')) {
            $remindTime = Mage::helper('cartreservation')->getReminderTime($source);
            
            $leftTime = $leftReservation - $remindTime;
            $leftTime = ($leftTime > 0)? $leftTime: 0;
        }

        return $leftTime;
    }

    public function save()
    {
        if (!$this->_loaded) {
            $this->_explodeAttr('child_quote_item_id')->_explodeAttr('child_product_id');
        }

        // prevent create and save if not allowed for this product
        if (!$this->_isAllowReservation()) {
            $this->_dataSaveAllowed = false;
            return $this;
        }

        $this->_implodeAttr('child_quote_item_id')->_implodeAttr('child_product_id');
        return parent::save();
    }

    protected function _isAllowReservation()
    {
        if (is_null($this->_allowedReservation)) {
            $product = Mage::getModel('catalog/product')->load($this->getProductId());
            $crEnable = $this->_isAllowByProduct($product);

            // if enabled or disabled
            if ($crEnable == self::INHERITED) {
                // Inherited
                $cIds = $product->getCategoryIds();
                if ($cIds) {
                    // foreach by all parents' categories of product and check if any parent set or him parents
                    foreach ($cIds as $cid) {
                        $cat = Mage::getModel('catalog/category')->load($cid);
                        $_crEnableCat = $this->_isAllowByCat($cat);
                        // if at least parent is enabled then product is enabled
                        if ($_crEnableCat === self::ENABLED) {
                            $crEnable = self::ENABLED;
                            break;
                        }

                        // If at end all parents will be inherited exept one or each disabled 
                        // then product will be disabled
                        if ($_crEnableCat === self::DISABLED) {
                            $crEnable = self::DISABLED;
                        }
                    }
                }
            }

            // if at end status is enabled or inherited then product is enabled
            $this->_allowedReservation = $crEnable != self::DISABLED;
        }

        return $this->_allowedReservation;
    }

    protected function _isAllowByProduct($mainProduct)
    {
        $childProductIds = $this->getChildProductId(); // return array of ids

        $crEnable = self::INHERITED;
        // check if not bundle. For bundle should check only main product
        if ($childProductIds && (count($childProductIds) == 1)) {
            $childProductId = reset($childProductIds);
            $product = Mage::getModel('catalog/product')->load($childProductId);
            
            if ($product) {
                $crEnable = (Mage::helper('cartreservation/product')->getManageStock($product))? (int)$product->getCrEnable(): self::DISABLED;
            }
        }

        if ($crEnable == self::INHERITED) {
            if ($mainProduct) {
                $crEnable = (Mage::helper('cartreservation/product')->getManageStock($mainProduct))? (int)$mainProduct->getCrEnable(): self::DISABLED;
            }
        }

        return $crEnable;
    }

    protected function _isAllowByCat($cat)
    {
        $catId = ($cat)? $cat->getId(): 0;

        if (! array_key_exists($catId, self::$_cacheCrStatusForCategories)) {
            $parentIds = $cat->getParentIds();
            
            do {
                if ($cat && $cat->getId()) {
                    $crEnable = (int)$cat->getCrEnable();
                    if ($crEnable != self::INHERITED) {
                        self::$_cacheCrStatusForCategories[$catId] = $crEnable;
                        return $crEnable;
                    }
                }

                $pid = array_pop($parentIds);
                if ($pid) {
                    $cat = Mage::getModel('catalog/category')->load($pid);
                }
            } while ($pid);

            self::$_cacheCrStatusForCategories[$catId] = self::INHERITED;
        }

        return self::$_cacheCrStatusForCategories[$catId];
    }

    protected function _implodeAttr($attr)
    {
        $val = $this->getData($attr);
        if (is_array($val)) {
            $this->setData($attr, implode(',', $val));
        }

        return $this;
    }

    protected function _explodeAttr($attr)
    {
        $val = $this->getData($attr);
        if (! is_array($val)) {
            $this->setData($attr, empty($val)? array(): explode(',', $val));
        }

        return $this;
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        $this->_explodeAttr('child_quote_item_id')->_explodeAttr('child_product_id');
        $this->_loaded = true;
    }
}
