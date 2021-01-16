<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */ 
class Amasty_Followup_Model_History extends Mage_Core_Model_Abstract
{
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SENT = 'sent';
    const STATUS_CANCEL = 'cancel';
    
    const REASON_BLACKLIST = 'blacklist';
    const REASON_EVENT = 'event';
    const REASON_ADMIN = 'admin';
    const REASON_NOT_SUBSCRIBED = 'not_subsribed';
    
    const NAME_XML_PATH = 'amfollowup/template/name';
    const EMAIL_XML_PATH = 'amfollowup/template/email';
    const CC_XML_PATH = 'amfollowup/template/cc';
    
    protected static $_cancelEventValidation = array();
    protected static $_cancelNotSubscribedValidation = array();
    protected static $_cancelBlacklistValidation = array();
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('amfollowup/history');
    }
    
    protected function _sendEmail($rule, $email = null){
        
        ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
        ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));
        
        $mail = new Zend_Mail('utf-8');
        
        if ((string)Mage::getConfig()->getNode('modules/Ebizmarts_Mandrill/active') == 'true') {
            $mandrill = Mage::getModel("ebizmarts_mandrill/email_template");
            if (!$mandrill)
                $mandrill = Mage::getModel("mandrill/email_template");

            $mail = $mandrill->getMail();
        }
        
        $recipient = Mage::getStoreConfig("amfollowup/test/recipient");
        $safeMode = Mage::getStoreConfig("amfollowup/test/safe_mode");
        $recipientValidated = !empty($recipient) && Zend_Validate::is($recipient, 'EmailAddress');
        
        if ($email) {
            $mail->addTo($email);
        } else if (intval($safeMode) === 1){
            
            if ($recipientValidated) {
                $mail->addTo($recipient);
            }
            
        } else {
            $mail->addTo($this->getEmail());
        }
        
        $mail->setBodyHTML($this->getBody());
        
        $mail->setSubject('=?utf-8?B?' . base64_encode($this->getSubject()) . '?=');
        
        $senderName = $rule->getSenderName() ? $rule->getSenderName() : Mage::getStoreConfig(self::NAME_XML_PATH, $this->getStoreId());
        
        $senderEmail = $rule->getSenderEmail() ? $rule->getSenderEmail() : Mage::getStoreConfig(self::EMAIL_XML_PATH, $this->getStoreId());

        $cc = $rule->getSenderCc() ? $rule->getSenderCc() : Mage::getStoreConfig(self::CC_XML_PATH, $this->getStoreId());

        if (!empty($cc)){
            $mail->addBcc($cc);
        }
        
        $mail->setFrom($senderEmail, $senderName);
        
        try {
            if ((string)Mage::getConfig()->getNode('modules/Amasty_Smtp/active') == 'true' &&
                !Mage::getStoreConfig('amsmtp/general/disable_delivery')) 
            {
                $transportFacade = Mage::getModel('amsmtp/transport');
                
                $mail->send($transportFacade->getTransport());
                
                Mage::helper('amsmtp')->log(array(
                    'subject'           => $this->getSubject(),
                    'body'              => $this->getBody(),
                    'recipient_name'    => $senderName,
                    'recipient_email'   => $this->getEmail(),
                    'template_code'     => "",
                    'status'            => Amasty_Smtp_Model_Log::STATUS_SENT,
                ), Amasty_Smtp_Model_Log::STATUS_SENT);
                
            } else if ((string)Mage::getConfig()->getNode('modules/Aschroder_SMTPPro/active') == 'true' &&
                            Mage::helper('smtppro')->isEnabled() && !Mage::getStoreConfigFlag('system/smtp/disable')

            ) {

                $transport = new Varien_Object();

                Mage::dispatchEvent('aschroder_smtppro_queue_before_send', array(
                    'mail' => $mail,
                    'transport' => $transport
                ));

                if ($transport->getTransport()) { // if set by an observer, use it
                    try{
                        $mail->send($transport->getTransport());
                    } catch (Exception $e){
                        $mail->send();
                    }

                } else {
                    $mail->send();
                }

            } else {
                $mail->send();
            }
        }
        catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
        
        return true;
    }
    
    function processItem($rule, $email = null){
        $this->setExecutedAt($this->date(Amasty_Followup_Model_Event_Basic::getCurrentExecution()));
        $this->setStatus(self::STATUS_PROCESSING);
        $this->save();

        if ($this->_sendEmail($rule, $email)){
            $this->setFinishedAt($this->date(Amasty_Followup_Model_Event_Basic::getCurrentExecution()));
            $this->setStatus(self::STATUS_SENT);
            $this->save();
        }
    }
    
    function initOrderItem($order, $quote){
        $this->addData(array(
           'order_id'  => $order->getId(),
           'increment_id' => $order->getIncrementId(),
           'store_id'  => $quote->getStoreId(),
           'email'  => $quote->getCustomerEmail(),
           'customer_id' => $quote->getCustomerId(),
           'customer_name' => $quote->getCustomerFirstname(). ' ' .$quote->getCustomerLastname()
        ));
        
        return $this;
    }
    
    function initCustomerItem($customer){
        $this->addData(array(
           'store_id'  => $customer->getStoreId(),
           'email'  => $customer->getEmail(),
           'customer_id' => $customer->getId(),
           'customer_name' => $customer->getFirstname(). ' ' .$customer->getLastname()
        ));
        
        return $this;
    }
    
    protected function _getCoupon($rule, $schedule)
    {
        $coupon = array(
            'code' => null,
            'id' => null
        );

        if ($schedule->getUseRule()){
            $generator = $rule->getCouponMassGenerator();
            $generator->setData(array(
                'rule_id' => $rule->getId(),
                'qty' => 1,
                'length' => 12,
                'format' => 'alphanum',
                'prefix' => '',
                'suffix' => '',
                'dash' => '0',
                'uses_per_coupon' => '0',
                'uses_per_customer' => '0',
                'to_date' => '',
            ));
            $generator->generatePool();
            $generated = $generator->getGeneratedCount();

            $collection = Mage::getResourceModel('salesrule/coupon_collection');

            $collection
                ->addFieldToFilter('main_table.rule_id', $rule->getId())
                ->getSelect()
                ->joinLeft(
                    array('history' => Mage::getSingleton('core/resource')->getTableName('amfollowup/history')),
                    'main_table.coupon_id = history.coupon_id',
                    array()
            )->where('history.history_id is null')
            ->order('main_table.coupon_id desc')
            ->limit(1);

            $items = $collection->getItems();

            if (count($items) > 0){
                $salesCoupon = end($items);

                $coupon['id'] = $salesCoupon->getId();
                $coupon['code'] = $salesCoupon->getCode();
            }

        } else if ($rule) {
            $coupon['code'] = $rule->getCouponCode();
        }

        return $coupon;
    }
    
    function createItem($schedule, $createdAt = null){
        
        $rule = $this->_getRule($schedule);
        $coupon = $this->_getCoupon($rule, $schedule);
        
        $createdAt =  $createdAt ? $createdAt : $this->date(Amasty_Followup_Model_Event_Basic::getCurrentExecution());
        
        $this->addData(array(
           'public_key' => uniqid(),
           'schedule_id' => $schedule->getId(),
           'rule_id' => $schedule->getRuleId(),
           'created_at' => $createdAt,
           'scheduled_at' => $this->date(strtotime($createdAt) + $schedule->getDelayedStart()),
           'status' => self::STATUS_PENDING,
           'sales_rule_id' => $rule ? $rule->getId() : null,
           'coupon_code' => $coupon['code'],
           'coupon_id' => $coupon['id'],
           'coupon_to_date' => $rule ? $rule->getToDate() : null,
           
        ));

        $this->save();
        
        return $this;
    }
    
    function saveEmail($email = array()){
        $this->addData(array(
            'subject' => $email['subject'],
            'body' => $email['body'],
        ));
        $this->save();
        
        return $this;
    }
    
    function unsubscribe(){
        $blacklist = Mage::getModel('amfollowup/blist')->load($this->getEmail(), 'email');
        
        $blacklist->setData(array(
            'blacklist_id' => $blacklist->getId(),
            'email' => $this->getEmail(),
            'created_at' => $this->date(time()),
        ));
        $blacklist->save();
    }
    
    function date($timestamp){
        return date('Y-m-d H:i:s', $timestamp);
    }
    
    protected function _getRule($schedule){
        
        $rule = NULL;
        if ($schedule->getUseRule()){
            $rule = Mage::getModel('salesrule/rule')->load($schedule->getSalesRuleId());
        } else if ($schedule->getCouponType()){
            $store = Mage::app()->getStore($this->getStoreId()); 

            $rule = $this->_createCoupon(
                    $store, 
                    $schedule
            );
        }
        
        return $rule;
        
    }
    
    protected function _getCouponToDate($days, $delayedStart){
        return date('Y-m-d', (time() + $days*24*3600 + $delayedStart) );
    }
    
    protected function _createCoupon($store, $schedule)
    {
        $rule = NULL;
        
      	$couponData = array();
        $couponData['name']      = 'Alert #' . $this->getId();
        $couponData['is_active'] = 1;
        $couponData['website_ids'] = array(0 => $store->getWebsiteId());
        $couponData['coupon_code'] = strtoupper(uniqid()); // todo check for uniq in DB
        $couponData['uses_per_coupon'] = 1;
        $couponData['uses_per_customer'] = 1;
        $couponData['from_date'] = ''; //current date

//        $days = Mage::getStoreConfig('catalog/adjcartalert/coupon_days', $store);
//        $date = Mage::helper('core')->formatDate(date('Y-m-d', time() + $days*24*3600));
        $couponData['to_date'] = $this->_getCouponToDate($schedule->getExpiredInDays(), $schedule->getDelayedStart());
        
        $couponData['uses_per_customer'] = 1;
        $couponData['coupon_type'] = 2;
        $couponData['stop_rules_processing'] = 0;

        
        $couponData['simple_action']   = $schedule->getCouponType();//Mage::getStoreConfig('catalog/adjcartalert/coupon_type', $store);
        $couponData['discount_amount'] = $schedule->getDiscountAmount();//Mage::getStoreConfig('catalog/adjcartalert/coupon_amount', $store);
        
        if ($schedule->getDiscountQty())
            $couponData['discount_qty'] = $schedule->getDiscountQty();
        
        if ($schedule->getDiscountStep())
            $couponData['discount_step'] = $schedule->getDiscountStep();
        
        if ($schedule->getPromoSku())
            $couponData['promo_sku'] = $schedule->getPromoSku();
        
        $couponData['conditions'] = array(
            '1' => array(
                'type'       => 'salesrule/rule_condition_combine',
                'aggregator' => 'all',
                'value'      => 1,
                'new_child'  =>'', 
            )
        );
        
        if ($schedule->getSubtotalGreaterThan()){
            $couponData['conditions']['1--1'] = array(
               'type'      => 'salesrule/rule_condition_address',
               'attribute' => 'base_subtotal',
               'operator'  => '>=',
               'value'     => $schedule->getSubtotalGreaterThan()
           );
        }
        
        $couponData['actions'] = array(
            1 => array(
                'type'       => 'salesrule/rule_condition_product_combine',
                'aggregator' => 'all',
                'value'      => 1,
                'new_child'  =>'', 
            )
        );
        
        //create for all customer groups
        $couponData['customer_group_ids'] = array();
        
        $customerGroups = Mage::getResourceModel('customer/group_collection')
            ->load();

        $found = false;
        foreach ($customerGroups as $group) {
            if (0 == $group->getId()) {
                $found = true;
            }
            $couponData['customer_group_ids'][] = $group->getId();
        }
        if (!$found) {
            $couponData['customer_group_ids'][] = 0;
        }
        
        try { 
            $rule = Mage::getModel('salesrule/rule')
                ->loadPost($couponData)
                ->save();      
        } 
        catch (Exception $e){
            //print_r($e); exit;
            $couponData['coupon_code'] = '';   
        }
        
        return $rule;
    }
    
    function massCancel($ids){
        $collection = $this->getCollection()
            ->addFieldToFilter('history_id', array('in' => $ids));
        foreach($collection as $history){
            $history->reason = Amasty_Followup_Model_History::REASON_ADMIN;
            $history->status = Amasty_Followup_Model_History::STATUS_CANCEL;
            $history->save();
        }
    }
    
    static function validateBlacklist($history){
        
        if (!isset(self::$_cancelBlacklistValidation[$history->getEmail()])){
            $blist = Mage::getModel("amfollowup/blist")->load($history->getEmail(), 'email');
            self::$_cancelBlacklistValidation[$history->getEmail()] = $blist->getId() === null;
        }
        
        return self::$_cancelBlacklistValidation[$history->getEmail()];
    }
    
    static function validateNotSubscribed($rule, $history){
        if (!isset(self::$_cancelNotSubscribedValidation[$rule->getId()]))
            self::$_cancelNotSubscribedValidation[$rule->getId()] = array();
        
        if (!isset(self::$_cancelNotSubscribedValidation[$rule->getId()][$history->getCustomerId()])){
            $subscriber = Mage::getModel("newsletter/subscriber")->load($history->getCustomerId(), 'customer_id');
            
            self::$_cancelNotSubscribedValidation[$rule->getId()][$history->getCustomerId()] =
                    $subscriber->getSubscriberStatus() != Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED;
        }
        
        return self::$_cancelNotSubscribedValidation[$rule->getId()][$history->getCustomerId()];  
    }
    
    static function validateCancelEvent($rule, $history){
        if (!isset(self::$_cancelEventValidation[$rule->getId()]))
            self::$_cancelEventValidation[$rule->getId()] = array();
        
        if (!isset(self::$_cancelEventValidation[$rule->getId()][$history->getEmail()])){
            foreach($rule->getCancelEvents() as $event){
                if ($event->validate($history)) {
                    self::$_cancelEventValidation[$rule->getId()][$history->getEmail()] = true;
                    break;
                } else {
                    self::$_cancelEventValidation[$rule->getId()][$history->getEmail()] = false;
                }
            }
        }
        
        if (!isset(self::$_cancelEventValidation[$rule->getId()][$history->getEmail()]))
            self::$_cancelEventValidation[$rule->getId()][$history->getEmail()] = false;
        
        return self::$_cancelEventValidation[$rule->getId()][$history->getEmail()];
    }
    
    function validateBeforeSent($rule){
        $this->reason = null;
        
        if (!self::validateBlacklist($this)){
            $this->reason = Amasty_Followup_Model_History::REASON_BLACKLIST;
        } else if ($rule->getToSubscribers() && self::validateNotSubscribed($rule, $this)){
            $this->reason = Amasty_Followup_Model_History::REASON_NOT_SUBSCRIBED;
        } else if (self::validateCancelEvent($rule, $this)){
            $this->reason = Amasty_Followup_Model_History::REASON_EVENT;
        }
        
        return $this->reason === NULL;
    }
    
    function cancelItem(){
        $this->status = Amasty_Followup_Model_History::STATUS_CANCEL;
        $this->save();
    }
}
?>