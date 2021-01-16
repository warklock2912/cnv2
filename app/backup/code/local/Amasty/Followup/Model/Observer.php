<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */
class Amasty_Followup_Model_Observer 
{
    protected static $_onCustomerSaveAfterChecked = false;
    protected static $_onNewsletterSubscriberSaveAfterChecked = false;
    
    function clearCoupons(){
        $allCouponsCollection = Mage::getModel('salesrule/rule')->getCollection();
        
        $allCouponsCollection->join(

            array('history' => 'amfollowup/history'),
            'main_table.rule_id = history.sales_rule_id', 
            array('history.history_id')
        );
        
        $allCouponsCollection->getSelect()->where(
            'main_table.to_date < ?', date('Y-m-d', time())
        );
        
        foreach ($allCouponsCollection->getItems() as $aCoupon) {
            $aCoupon->delete();
        }
    }

    public function clearHistory()
    {
        $period = Mage::getStoreConfig('amfollowup/general/clean_up_period');
        if ($period > 0) {
            $historyCollection = Mage::getModel('amfollowup/history')->getCollection();

            $historyCollection->getSelect()->where(
                'main_table.finished_at < ?', date('Y-m-d', strtotime('-' . $period . ' day'))
            );

            foreach ($historyCollection->getItems() as $history) {
                $history->delete();
            }
        }
    }
    
    function refreshHistory(){
        Mage::getModel('amfollowup/schedule')->run(TRUE);
    }
    
    function onCustomerSaveAfter($observer){
        
        $customer = $observer->getCustomer();
        
        if (!self::$_onCustomerSaveAfterChecked) {
            
            $customer->setTargetCreatedAt($customer->getCreatedAt());
            
            Mage::getModel('amfollowup/schedule')->checkCustomerRules($customer, array(
                Amasty_Followup_Model_Rule::TYPE_CUSTOMER_GROUP,
                Amasty_Followup_Model_Rule::TYPE_CUSTOMER_NEW
            ));  
            self::$_onCustomerSaveAfterChecked = true;
        }
    }
    
    function onNewsletterSubscriberSaveAfter($observer){
        $subscriber = $observer->getSubscriber();
        if (!self::$_onNewsletterSubscriberSaveAfterChecked && !$subscriber->getChangeStatusAt()) {
            $customer = NULL;
            if (!$subscriber->getCustomerId()){
                
                $customer = Mage::getModel('customer/customer');
                $customer->addData(array(
                    "email" => $subscriber->getSubscriberEmail(),
                    "store_id" => $subscriber->getStoreId(),
                ));
                
            } else {
            $customer = Mage::getModel('customer/customer')->load($subscriber->getCustomerId());
            }
            
            Mage::getModel('amfollowup/schedule')->checkSubscribtionRules($subscriber, $customer, array(
                Amasty_Followup_Model_Rule::TYPE_CUSTOMER_SUBSCRIPTION
            ));  
            
            self::$_onNewsletterSubscriberSaveAfterChecked = true;
            $subscriber->setChangeStatusAt(date("Y-m-d H:i:s"));
            $subscriber->save();
        }
    }
    
    function onWishlistShare($observer){
        $wishlist = $observer->getWishlist();
        $customer = Mage::getModel('customer/customer')->load($wishlist->getCustomerId());
        
        Mage::getModel('amfollowup/schedule')->checkCustomerRules($customer, array(
            Amasty_Followup_Model_Rule::TYPE_CUSTOMER_WISHLIST_SHARED
        ));
    }
    
    function onSalesruleValidatorProcess($observer)
    {
        
        $ret = true;
        $ruleId = $observer->getEvent()->getRule()->getRuleId();

        $history = null;

        foreach(Mage::getModel("amfollowup/history")->getCollection()
                    ->addFieldToFilter("sales_rule_id", $ruleId) as $item){
            if ($item->getCouponCode() == $observer->getEvent()->getRule()->getCode()){
                $history = $item;
                break;
            }
        }
        
        if ($history && $history->getId()){
            
            $customerEmail = $history->getCustomerId() ?
                    $observer->getEvent()->getQuote()->getCustomer()->getEmail() :
                    $observer->getEvent()->getQuote()->getBillingAddress()->getEmail()
                ;

            $customerCoupon = Mage::getStoreConfig("amfollowup/general/customer_coupon");
            if ($customerCoupon && $customerEmail != $history->getEmail()) {
                $observer->getEvent()->getQuote()->setCouponCode("");
            }
        }
        return $ret;
    }
    
}