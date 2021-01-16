<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */ 
class Amasty_Followup_Model_Rule extends Mage_Rule_Model_Rule
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    
    const COUPON_CODE_NONE = '';
    const COUPON_CODE_BY_PERCENT = 'by_percent';
    const COUPON_CODE_BY_FIXED = 'by_fixed';
    const COUPON_CODE_CART_FIXED = 'cart_fixed';
    
    const TYPE_ORDER_NEW = 'order_new';
    const TYPE_ORDER_SHIP = 'order_ship';
    const TYPE_ORDER_INVOICE = 'order_invoice';
    const TYPE_ORDER_COMPLETE = 'order_complete';
    const TYPE_ORDER_CANCEL = 'order_cancel';
    
    const TYPE_CUSTOMER_GROUP = 'customer_group';
    const TYPE_CUSTOMER_BIRTHDAY = 'customer_birthday';
    const TYPE_CUSTOMER_NEW = 'customer_new';
    const TYPE_CUSTOMER_SUBSCRIPTION = 'customer_subscription';
    const TYPE_CUSTOMER_ACTIVITY = 'customer_activity';
    const TYPE_CUSTOMER_WISHLIST = 'customer_wishlist';
    const TYPE_CUSTOMER_WISHLIST_SHARED = 'customer_wishlist_shared';
    const TYPE_CUSTOMER_DATE = 'customer_date';
    
    const TYPE_CANCEL_ORDER_COMPLETE = 'cancel_order_complete';
    const TYPE_CANCEL_ORDER_STATUS = 'cancel_order_status';
    const TYPE_CANCEL_CUSTOMER_LOGGEDIN = 'cancel_customer_loggedin';
    const TYPE_CANCEL_CUSTOMER_CLICKLINK = 'cancel_customer_clicklink';
    const TYPE_CANCEL_CUSTOMER_WISHLIST_SHARED = 'cancel_customer_wishlist_shared';

    public function _construct()
    {
        parent::_construct();
        $this->_init('amfollowup/rule');
    }
    
    function isOrderRelated(){
        return in_array($this->getStartEventType(), array(
            self::TYPE_ORDER_NEW,
            self::TYPE_ORDER_SHIP,
            self::TYPE_ORDER_INVOICE,
            self::TYPE_ORDER_COMPLETE,
            self::TYPE_ORDER_CANCEL,
        ));
                
    }
    
    public function getConditionsInstance()
    {
        return Mage::getModel('amfollowup/rule_condition_combine');
    }
    
    protected function _toSeconds($days, $hours, $minutes){
        return $minutes * 60 + ($hours * 60 * 60) + ($days * 24 * 60 * 60);
    }
    
    protected function _afterSave()
    {
        if (is_array($this->getSchedule())){
            $scheduleCollection = Mage::getModel('amfollowup/schedule')->getCollection();
            $scheduleCollection->addFilter('rule_id', $this->getId());
            
            $scheduleDbData = $scheduleCollection->getItems();
            
            $schedule = $this->getSchedule();
            $saveData = array();
            
                
            foreach($schedule['row_index'] as $order){
                
                if ($order != '_rowIdx_') { //skip first template row
                    
                    $days = intval($schedule['days'][$order]);
                    $hours = intval($schedule['hours'][$order]);
                    $minutes = intval($schedule['minutes'][$order]);
                    
                    $rowIndex = intval($schedule['row_index'][$order]);
                    $email_template_id = $schedule['email_templates'][$order];

                    $delayed_start = $this->_toSeconds($days, $hours, $minutes);
                    $saveItem = array();
                    if (array_key_exists('use_rule', $schedule) && intval($schedule['use_rule'][$order]) == 1) {
                        $saveItem = array(
                            "use_rule" => 1,
                            "sales_rule_id" => $schedule['rule_id'][$order],
                            'delayed_start' => $delayed_start,
                            'email_template_id' => empty($email_template_id) ? NULL : $email_template_id,
                        );
                    } else {
                    $coupon_type = $schedule['coupon_type'][$order];
                    $discount_amount = intval($schedule['discount_amount'][$order]);
                    
                    $expired_in_days = intval($schedule['expired_in_days'][$order]);
                    $discount_qty = $schedule['discount_qty'][$order];
                    $discount_step = $schedule['discount_step'][$order];
                    $promo_sku = $schedule['promo_sku'][$order];
                    $ampromo_type = $schedule['ampromo_type'][$order];
                    $subtotal_greater_than = $schedule['subtotal_greater_than'][$order];
                    
                        $saveItem = array(
                        'email_template_id' => empty($email_template_id) ? NULL : $email_template_id,
                            'delayed_start' => $delayed_start,
                        'coupon_type' => empty($coupon_type) ? NULL : $coupon_type,
                        'discount_amount' => $discount_amount,
                        'expired_in_days' => $expired_in_days,
                        'discount_qty' => $discount_qty,
                        'discount_step' => $discount_step,
                        'promo_sku' => $promo_sku,
                        'ampromo_type' => $ampromo_type,
                            'use_rule' => 0,
                        'subtotal_greater_than' => empty($subtotal_greater_than) ? NULL : $subtotal_greater_than,
                    );
                    }
                        
                    $saveData[$order] = $saveItem;
                }
            }
            
            if (count($saveData) == 0) {
                Mage::throwException('Schedule should be completed');
            }
            
            foreach($scheduleDbData as $scheduleDbItem){
                $delayed_start = $scheduleDbItem->getDelayedStart();
                
                if (array_key_exists($delayed_start, $saveData)){
                    
//                    $scheduleDbItem->setEmailTemplateId($saveData[$delayed_start]);
                    $scheduleDbItem->addData($saveData[$delayed_start]);
                    
                    $scheduleDbItem->save();
                    unset($saveData[$delayed_start]);
                } else {
                    $scheduleDbItem->delete();
                }
                
            }
            
            
            foreach($saveData as $delayed_start => $config){

                $schedule = Mage::getModel('amfollowup/schedule');
                $schedule->setData(array_merge(array(
                    'rule_id' => $this->getId(),
                    'email_template_id' => $email_template_id
                ), $config));

                $schedule->save();
            }
        } 
        
        //Saving attributes used in rule
        $ruleProductAttributes = array_merge(
            $this->_getUsedAttributes($this->getConditionsSerialized()),
            $this->_getUsedAttributes($this->getActionsSerialized())
        );
                
        if (count($ruleProductAttributes)) {
            $this->getResource()->saveAttributes($this->getId(), $ruleProductAttributes);
        } 
        
        return parent::_afterSave(); 
    }
    
    /**
     * Return all product attributes used on serialized action or condition
     *
     * @param string $serializedString
     * @return array
     */
    protected function _getUsedAttributes($serializedString)
    {
        $result = array();
        
        $pattern = '~s:32:"salesrule/rule_condition_product";s:9:"attribute";s:\d+:"(.*?)"~s';
        $matches = array();
        if (preg_match_all($pattern, $serializedString, $matches)){
            foreach ($matches[1] as $attributeCode) {
                $result[] = $attributeCode;
            }
        }
        
        return $result;
    }
    
    function validateAddress($quote){
        $ret = false;
        
        foreach($quote->getAllAddresses() as $address){

            $address->setCollectShippingRates(true);
            try{
                $address->collectShippingRates();
            } catch(Exception $e){

            }
            $this->_initAddress($address, $quote);
        
            if (parent::validate($address)){
                $ret = true;
                break;
            }
        }
        return $ret;
    }
    
    function getStartEvent(){
        
        $ret = null;
        switch ($this->getStartEventType()){
            case self::TYPE_ORDER_NEW:
                $ret = new Amasty_Followup_Model_Event_Order_New($this);
            break;
            case self::TYPE_ORDER_SHIP:
                $ret = new Amasty_Followup_Model_Event_Order_Ship($this);
            break;
            case self::TYPE_ORDER_INVOICE:
                $ret = new Amasty_Followup_Model_Event_Order_Invoice($this);
            break;
            case self::TYPE_ORDER_COMPLETE:
                $ret = new Amasty_Followup_Model_Event_Order_Complete($this);
            break;
            case self::TYPE_ORDER_CANCEL:
                $ret = new Amasty_Followup_Model_Event_Order_Cancel($this);
            break;
            case self::TYPE_CUSTOMER_GROUP:
                $ret = new Amasty_Followup_Model_Event_Customer_Group($this);
            break;
            case self::TYPE_CUSTOMER_BIRTHDAY:
                $ret = new Amasty_Followup_Model_Event_Customer_Birthday($this);
            break;
            case self::TYPE_CUSTOMER_DATE:
                $ret = new Amasty_Followup_Model_Event_Customer_Date($this);
            break;
            case self::TYPE_CUSTOMER_NEW:
                $ret = new Amasty_Followup_Model_Event_Customer_New($this);
            break;
            case self::TYPE_CUSTOMER_SUBSCRIPTION:
                $ret = new Amasty_Followup_Model_Event_Customer_Subscription($this);
            break;
            case self::TYPE_CUSTOMER_ACTIVITY:
                $ret = new Amasty_Followup_Model_Event_Customer_Activity($this);
            break;
            case self::TYPE_CUSTOMER_WISHLIST:
                $ret = new Amasty_Followup_Model_Event_Customer_Wishlist($this);
            break;
            case self::TYPE_CUSTOMER_WISHLIST_SHARED:
                $ret = new Amasty_Followup_Model_Event_Customer_Wishlist_Shared($this);
            break;
            default:
                $ret = new Amasty_Followup_Model_Event_Basic($this);
            break;
        }
        return $ret;
    }
        
    function getCancelEvents(){
        
        $ret = array();
        $hlr = Mage::helper("amfollowup");
        
        $cancelTypes = explode(",", $this->getCancelEventType());
        
        foreach($cancelTypes as $cancelEventType){
            switch ($cancelEventType){
                case self::TYPE_CANCEL_ORDER_COMPLETE:
                    $ret[] = new Amasty_Followup_Model_Event_Cancel_Order_Complete($this);
                break;
                case self::TYPE_CANCEL_CUSTOMER_LOGGEDIN:
                    $ret[] = new Amasty_Followup_Model_Event_Cancel_Customer_Loggedin($this);
                break;
                case self::TYPE_CANCEL_CUSTOMER_CLICKLINK:
                    $ret[] = new Amasty_Followup_Model_Event_Cancel_Customer_Clicklink($this);
                break;
                case self::TYPE_CANCEL_CUSTOMER_WISHLIST_SHARED:
                    $ret[] = new Amasty_Followup_Model_Event_Cancel_Customer_Wishlist_Shared($this);
                break;
            }
        }
        
        if ($this->isOrderRelated()){
            foreach(Mage::getResourceModel('sales/order_status_history') as $status){
                $eventKey = $hlr->getOrderCancelEventKey($status);
                
                if (in_array($eventKey, $cancelTypes)){
                    
                    $ret[] = new Amasty_Followup_Model_Event_Cancel_Order_Status($this, $status);
                }
            }
        }
        
        return $ret;
    }
    
    
    protected function _initAddress($address, $quote){
    
        $address->setData('total_qty', $quote->getData('items_qty'));
        return $address;
    }

    protected function _setWebsiteIds(){
        $websites = array();

        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $websites[$website->getId()] = $website->getId();
                }
            }
        }

        $this->setOrigData('website_ids', $websites);
    }

    protected function _beforeSave(){
        $this->_setWebsiteIds();
        return parent::_beforeSave();
    }

    protected function _beforeDelete(){
        $this->_setWebsiteIds();
        return parent::_beforeDelete();
    }
}