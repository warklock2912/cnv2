<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */ 
class Amasty_Followup_Model_Schedule extends Mage_Core_Model_Abstract
{
    protected $_customerLog = array();
    protected $_customerGroup = array();
    
    protected $_scheduleCollections = array();
    protected $_rules = array();
    
    const PROCESS_BUSY = 'amfollowup/common/process_busy';
    
    function getDays(){
        return $this->getDelayedStart() > 0 ? 
                floor($this->getDelayedStart() / 24 / 60 / 60) :
                NULL;
    }
    
    function getHours(){
        $days = $this->getDays();
        $time = $this->getDelayedStart() - ($days * 24 * 60 * 60);
        
        return $time > 0 ? 
                floor($time / 60 / 60) :
                NULL;
    }
    
    function getMinutes(){
        $days = $this->getDays();
        $hours = $this->getHours();
        $time = $this->getDelayedStart() - ($days * 24 * 60 * 60) - ($hours * 60 * 60);
        
        return $time > 0 ? 
                floor($time / 60) :
                NULL;
    }
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('amfollowup/schedule');
    }
    
    function run($cronExecution = FALSE){
        Amasty_Followup_Model_Event_Basic::clear();

        $this->_prepareOrderRules();
        $this->_prepareCustomerRules();

        $this->_process();        
    }
    
    protected function _getScheduleCollection($rule){
        if (!isset($this->_scheduleCollections[$rule->getId()])){
            $this->_scheduleCollections[$rule->getId()] = Mage::getModel('amfollowup/schedule')
                    ->getCollection()
                    ->addRule($rule);
        }
        
        return $this->_scheduleCollections[$rule->getId()];
    }
    
    protected function _getRuleCollection($types = array()){
        $ruleCollection = Mage::getModel('amfollowup/rule')
                ->getCollection()
                ->addStartFilter($types);
        
        return $ruleCollection;
    }

    protected function _getQuoteCollection($ids){
        $quoteCollection = Mage::getModel('sales/quote')->getCollection();
        $quoteCollection->addFieldToFilter('entity_id', array('in' => $ids));
        return $quoteCollection;
    }
    
    protected function _getOrderCollection($ids){
        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $orderCollection->addFieldToFilter('entity_id', array('in' => $ids));
        return $orderCollection;
    }

    protected function _loadCustomerLog($customer){
        
        if (!isset($this->_customerLog[$customer->getId()])){
            $logCustomer = null;

            if (version_compare(Mage::getVersion(), '1.5', '>')) {
                $logCustomer = Mage::getModel('log/customer')->loadByCustomer($customer);
            } else {
                $logCustomer = Mage::getModel('log/customer')->load($customer->getId());
            }

            $this->_customerLog[$customer->getId()] = $logCustomer;
            $this->_customerLog[$customer->getId()]->setInactiveDays(floor((time() - strtotime($this->_customerLog[$customer->getId()]->getLoginAt())) / 60 / 60 / 24));
            
        }
        return $this->_customerLog[$customer->getId()];
    }
    
    protected function _loadCustomerGroup($id){
        if (!isset($this->_customerGroup[$id])){
            $this->_customerGroup[$id] = Mage::getModel('customer/group')->load($id);
        }
        
        return $this->_customerGroup[$id];
    }
    
    protected function _getCustomerEmailVars($customer, $history){
        $logCustomer = $this->_loadCustomerLog($customer);
        $customerGroup = $this->_loadCustomerGroup($customer->getGroupId());
        
        return array(
            Amasty_Followup_Model_Formatmanager::TYPE_CUSTOMER => $customer,
            Amasty_Followup_Model_Formatmanager::TYPE_CUSTOMER_GROUP => $customerGroup,
            Amasty_Followup_Model_Formatmanager::TYPE_CUSTOMER_LOG => $logCustomer,
            Amasty_Followup_Model_Formatmanager::TYPE_HISTORY => $history,
        );
        
    }
    
    protected function _getOrderEmailVars($order, $quote, $customer, $history){
        $vars = $this->_getCustomerEmailVars($customer, $history);
        $vars[Amasty_Followup_Model_Formatmanager::TYPE_ORDER] = $order;
        $vars[Amasty_Followup_Model_Formatmanager::TYPE_QUOTE] = $quote;
        return $vars;
    }
    
    
    function checkCustomerRules($customer, $types = array()){
                
        $ruleCollection = $this->_getRuleCollection($types);
        foreach($ruleCollection as $rule){
            
            $event = $rule->getStartEvent();
            
            if ($event->validate($customer)){
                $this->createCustomerHistory($rule, $event, $customer);
            }
        }
    }
    
    
    function checkSubscribtionRules($subscriber, $customer, $types = array()){
                
        $ruleCollection = $this->_getRuleCollection($types);
        
        foreach($ruleCollection as $rule){
            
            $event = $rule->getStartEvent();
            
            if ($event->validateSubscription($subscriber, $customer)){
                
                $this->createCustomerHistory($rule, $event, $customer);
            }
        }
    }
    
    public function createCustomerHistory($rule, $event, $customer){
        $ret = array();
        $scheduleCollection = $this->_getScheduleCollection($rule);
                
        foreach($scheduleCollection as $schedule){
            Mage::app()->setCurrentStore($customer->getStoreId());

            $history = Mage::getModel("amfollowup/history");

            $history->initCustomerItem($customer);

            $history->createItem($schedule, $customer->getTargetCreatedAt());

            $email = $event->getEmail($schedule, $history, $this->_getCustomerEmailVars($customer, $history));
            $history->saveEmail($email);

            $ret[] = $history;
        }
        return $ret;
    }
    
    protected function _prepareCustomerRules(){
        
        $ruleCollection = $this->_getRuleCollection(array(
            Amasty_Followup_Model_Rule::TYPE_CUSTOMER_BIRTHDAY,
            Amasty_Followup_Model_Rule::TYPE_CUSTOMER_DATE,
            Amasty_Followup_Model_Rule::TYPE_CUSTOMER_ACTIVITY,
            Amasty_Followup_Model_Rule::TYPE_CUSTOMER_WISHLIST,
        ));

        foreach($ruleCollection as $rule){
            
            $event = $rule->getStartEvent();
        
            $customerCollection = $event->getCollection();
            
            foreach($customerCollection as $customer){
                
                if ($event->validate($customer)){
                    $this->createCustomerHistory($rule, $event, $customer);
                }
            }   
        }
    }
    
    public function createOrderHistory($rule, $event, $order, $quote, $customer){
        $ret = array();
        $scheduleCollection = $this->_getScheduleCollection($rule);

        if (!$customer->getId()){
           $this->_initCustomer($customer, $quote);
        }
        
        foreach($scheduleCollection as $schedule){
            Mage::app()->setCurrentStore($quote->getStoreId());
            $history = Mage::getModel("amfollowup/history");

            $history->initOrderItem($order, $quote);

            $history->createItem($schedule, $quote->getTargetCreatedAt());

            $email = $event->getEmail($schedule, $history, $this->_getOrderEmailVars($order, $quote, $customer, $history));

            $history->saveEmail($email);

            $ret[] = $history;
        }
        
        return $ret;
    }
    
    public function _prepareOrderRules(){
        $ruleCollection = $this->_getRuleCollection(array(
            Amasty_Followup_Model_Rule::TYPE_ORDER_NEW,
            Amasty_Followup_Model_Rule::TYPE_ORDER_SHIP,
            Amasty_Followup_Model_Rule::TYPE_ORDER_INVOICE,
            Amasty_Followup_Model_Rule::TYPE_ORDER_COMPLETE,
            Amasty_Followup_Model_Rule::TYPE_ORDER_CANCEL
        ));
        
        foreach($ruleCollection as $rule){
            $event = $rule->getStartEvent();
            
            $quoteCollection = $event->getCollection();
            
            foreach($quoteCollection as $quote){
                if ($event->validate($quote)) {
                    $order = Mage::getModel("sales/order")->load($quote->getOrderId());
                    $customer = Mage::getModel("customer/customer")->load($quote->getCustomerId());
                    $this->createOrderHistory($rule, $event, $order, $quote, $customer);
                }
            }
        }
    }
    
    public function _initCustomer(&$customer, $quote){
        
        $quoteData = $quote->getData();
        
        foreach($quoteData as $key => $val){
            if (strpos($key, "customer_") !== FALSE){
                $customer->setData(str_replace("customer_", "", $key), $val);
            }
        }
    }
    
    protected function _getHistoryCollection(){
        $historyCollection = Mage::getModel('amfollowup/history')
                ->getCollection()
                ->addOrderData();
        
        return $historyCollection;
    }
    
    protected function _loadRule($ruleId){
        
        if (!isset($this->_rules[$ruleId])){
            $this->_rules[$ruleId] = Mage::getModel("amfollowup/rule")->load($ruleId);
        }
        
        return $this->_rules[$ruleId];
    }
    
    protected function _process(){
        $historyCollection = $this->_getHistoryCollection()
                ->addReadyFilter($this->date(Amasty_Followup_Model_Event_Basic::getCurrentExecution()));
        foreach($historyCollection as $history){
            
            $rule = $this->_loadRule($history->getRuleId());
            
            if ($history->validateBeforeSent($rule)) {
                $history->processItem($rule);
            } else {
                $history->cancelItem();
            }
        }
    }
    
    function date($timestamp){
        return date('Y-m-d H:i:s', $timestamp);
    }
}
?>