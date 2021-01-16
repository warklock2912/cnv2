<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Payrestriction
 */ 
class Amasty_Payrestriction_Helper_Payment_Data extends Mage_Payment_Helper_Data
{
    protected $_allRules = null;

    public function getStoreMethods($store = null, $quote = null)
    {
        
        $methods = parent::getStoreMethods($store, $quote);
        if (!$quote){
            return $methods;
        }
        
        $address = $quote->getShippingAddress();
        $items   = $quote->getAllItems();
        foreach ($methods as $k => $method){
            foreach ($this->getRules($address, $items) as $rule){
               if ($rule->restrict($method)){
                   if ($rule->validate($address)
                       && $this->isCouponValid($quote, $rule)
                       && !$this->isCouponValid($quote, $rule, true)
                   ){
                       unset($methods[$k]);
                   }//if validate
               }//if restrict
            }//rules        
        }//methods
        
        return $methods;
    }

    public function isCouponValid($quote, $rule, $isDisable = false)
    {
        if (!$isDisable) {
            $code = $rule->getCoupon();
            $discountId = $rule->getDiscountId();
        } else {
            $code = $rule->getCouponDisable();
            $discountId = $rule->getDiscountIdDisable();
        }

        $actualCouponCode  = trim(strtolower($code));
        $actualDiscountId  = intVal($discountId);

        if (!$actualCouponCode && !$actualDiscountId) {
            if (!$isDisable) {
                return true;
            } else {
                return false;
            }
        }
        $providedCouponCodes = $this->getCouponCodes($quote);

        if ($actualCouponCode){
            return (in_array($actualCouponCode, $providedCouponCodes));
        }

        if ($actualDiscountId){
            foreach ($providedCouponCodes as $code){
                $couponModel         = Mage::getModel('salesrule/coupon')->load($code, 'code');
                $providedDiscountId  = $couponModel->getRuleId();

                if ($providedDiscountId == $actualDiscountId){
                    return true;
                }
                $couponModel = null;
            }

        }

        return false;
    }

    public function getCouponCodes($quote)
    {
        $codes = $quote->getCouponCode();

        if (!$codes)
            return array();

        $providedCouponCodes = explode(",",$codes);

        foreach ($providedCouponCodes as $key => $code){
            $providedCouponCodes[$key] = trim($code);
        }

        return $providedCouponCodes;

    }

    /**
     * @param $address
     * @param $items - products in cart
     * @return $this|null
     */
    public function getRules($address, $items)
    {
        if (is_null($this->_allRules)){
            $this->_allRules = Mage::getModel('ampayrestriction/rule')
                ->getCollection()
                ->addAddressFilter($address)
             ;
             if ($this->_isAdmin()){
                 $this->_allRules->addFieldToFilter('for_admin', 1);
             }

            $hasBackOrders = false;
            $hasNoBackOrders = false;
            foreach ($items as $item){
                if ($item->getBackorders() > 0 ){
                    $hasBackOrders = true;
                } else {
                    $hasNoBackOrders = true;
                }
                if ($hasBackOrders && $hasNoBackOrders) {
                    break;
                }
            }

            if ($hasNoBackOrders && $hasNoBackOrders) {
                $outOfStockOption = Amasty_Payrestriction_Model_Rule::ALL_ORDERS;
            } elseif ($hasBackOrders) {
                $outOfStockOption = Amasty_Payrestriction_Model_Rule::BACKORDERS_ONLY;
            } else {
                $outOfStockOption = Amasty_Payrestriction_Model_Rule::NON_BACKORDERS;
            }

            if (!$hasBackOrders){
                $this->_allRules->addFieldToFilter('out_of_stock',  $outOfStockOption);
            }

            $this->_allRules->load();

            foreach ($this->_allRules as $rule){

                $rule->afterLoad(); 
            }
        }
        
        return  $this->_allRules;
    }
    
    protected function _isAdmin()
    {
        if (Mage::app()->getStore()->isAdmin())
            return true;
        // for some reason isAdmin does not work here
        if (Mage::app()->getRequest()->getControllerName() == 'sales_order_create')
            return true;
            
        return false;
    }    
    
}