<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */
class Amasty_Followup_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    public function getRuleStatuses()
    {
        return array(
                Amasty_Followup_Model_Rule::STATUS_ACTIVE => Mage::helper('salesrule')->__('Active'),
                Amasty_Followup_Model_Rule::STATUS_INACTIVE => Mage::helper('salesrule')->__('Inactive'),
            );       
    }
    
    public function getCouponTypes(){
        $types = array(
            Amasty_Followup_Model_Rule::COUPON_CODE_NONE => Mage::helper('amfollowup')->__('-- None --'),
            Amasty_Followup_Model_Rule::COUPON_CODE_BY_PERCENT => Mage::helper('amfollowup')->__('Percent of product price discount'),
            Amasty_Followup_Model_Rule::COUPON_CODE_BY_FIXED => Mage::helper('amfollowup')->__('Fixed amount discount'),
            Amasty_Followup_Model_Rule::COUPON_CODE_CART_FIXED => Mage::helper('amfollowup')->__('Fixed amount discount for whole cart'),
        ); 
        
        
         if (Mage::getConfig()->getNode('modules/Amasty_Rules/active') == 'true') {
             $amrules = Mage::helper("amrules")->getDiscountTypes(true);
             unset($amrules['setof_percent']);
             unset($amrules['setof_fixed']);
             $types = array_merge($types, $amrules);
         }
         
         if (Mage::getConfig()->getNode('modules/Amasty_Promo/active' ) == 'true') {
                
             $types = array_merge($types, array(
                 'ampromo_items' => Mage::helper('ampromo')->__('Auto add promo items with products'),
                 'ampromo_cart' => Mage::helper('ampromo')->__('Auto add promo items for the whole cart'),
                 'ampromo_product' => Mage::helper('ampromo')->__('Auto add the same product')
             ));
         }
        
        return $types;
    }
    
    public function getAllGroups()
    {
        $customerGroups = Mage::getResourceModel('customer/group_collection')
            ->load()->toOptionArray();

        $found = false;
        foreach ($customerGroups as $group) {
            if ($group['value']==0) {
                $found = true;
            }
        }
        if (!$found) {
            array_unshift($customerGroups, array('value'=>0, 'label'=>Mage::helper('salesrule')->__('NOT LOGGED IN')));
        } 
        
        return $customerGroups;
    }
    
    public function getEmailTemplatesOptions($type){
        $collection = Mage::getResourceModel('core/email_template_collection')
                ->addFilter("orig_template_code", "amfollowup_" . $type)
                ->load();
        
        $options = $collection->toOptionArray();
        return $options;
    }
    
    public function getOrderStatuses()
    {
        $statuses = Mage::getResourceModel('sales/order_status_history')
            ->toOptionHash();
        
        return $statuses;
        //array_merge(array("" => $this->__("--- None ---")), $statuses);
    }
        
    public function getHistoryStatusSent(){
        return array(
            '' => $this->__('-- None --'),
            Amasty_Followup_Model_History::STATUS_PROCESSING => $this->__('No'),
            Amasty_Followup_Model_History::STATUS_SENT => $this->__('Yes'),
            Amasty_Followup_Model_History::STATUS_CANCEL => $this->__('No'),
        ); 
    }
    
    public function getEventTypes(){
        return 
            array(
                    array(
                      'label' => $this->__('Order'),
                      'value' => array
                        (
                            array
                            (
                                'label' => 'Created',
                                'value' => Amasty_Followup_Model_Rule::TYPE_ORDER_NEW
                            ),
                            array
                            (
                                'label' => 'Shipped',
                                'value' => Amasty_Followup_Model_Rule::TYPE_ORDER_SHIP
                            ),
                            array
                            (
                                'label' => 'Invoiced',
                                'value' => Amasty_Followup_Model_Rule::TYPE_ORDER_INVOICE
                            ),
                            array
                            (
                                'label' => 'Completed',
                                'value' => Amasty_Followup_Model_Rule::TYPE_ORDER_COMPLETE
                            ),
                            array
                            (
                                'label' => 'Cancelled',
                                'value' => Amasty_Followup_Model_Rule::TYPE_ORDER_CANCEL
                            ),
                        )
                    ),
                    array(
                      'label' => $this->__('Customer'),
                      'value' => array
                        (
                            array
                            (
                                'label' => 'No Activity',
                                'value' => Amasty_Followup_Model_Rule::TYPE_CUSTOMER_ACTIVITY
                            ),
                            array
                            (
                                'label' => 'Changed Group',
                                'value' => Amasty_Followup_Model_Rule::TYPE_CUSTOMER_GROUP
                            ),
                            array
                            (
                                'label' => 'Subscribed  to Newsletter',
                                'value' => Amasty_Followup_Model_Rule::TYPE_CUSTOMER_SUBSCRIPTION
                            ),
                            array
                            (
                                'label' => 'Birthday',
                                'value' => Amasty_Followup_Model_Rule::TYPE_CUSTOMER_BIRTHDAY
                            ),
                            array
                            (
                                'label' => 'Registration',
                                'value' => Amasty_Followup_Model_Rule::TYPE_CUSTOMER_NEW
                            ),
                        )
                    ),
                    array(
                      'label' => $this->__('Wishlist'),
                      'value' => array
                        (
                            array
                            (
                                'label' => 'Product Added',
                                'value' => Amasty_Followup_Model_Rule::TYPE_CUSTOMER_WISHLIST
                            ),
                            array
                            (
                                'label' => 'Shared',
                                'value' => Amasty_Followup_Model_Rule::TYPE_CUSTOMER_WISHLIST_SHARED
                            ),
                         )
                    ),
                    array(
                      'label' => $this->__('Date'),
                      'value' => array
                        (
                            array
                            (
                                'label' => 'Date',
                                'value' => Amasty_Followup_Model_Rule::TYPE_CUSTOMER_DATE
                            )
                         )
                    )
        );
    }
    
    function getOrderCancelEventKey($status){
        return Amasty_Followup_Model_Rule::TYPE_CANCEL_ORDER_STATUS . $status->getStatus();
    }
    
    function getOrderCancelEvents(){
        $ret = array();

        foreach(Mage::getResourceModel('sales/order_status_history') as $status){
            $ret[$this->getOrderCancelEventKey($status)] = $this->__('Order Becomes: %s', $status->getLabel());   
        }
        
        return $ret;
    }
    
    public function getCancelTypes($useOrderEvents = FALSE){
        $otherEvents = array();
        
        if ($useOrderEvents)
            $otherEvents = array_merge ($this->getOrderCancelEvents(), $otherEvents);
        
        return array_merge(array(
            
            Amasty_Followup_Model_Rule::TYPE_CANCEL_CUSTOMER_LOGGEDIN => $this->__('Customer logged in'),
            Amasty_Followup_Model_Rule::TYPE_CANCEL_ORDER_COMPLETE => $this->__('New Order Placed'),
            Amasty_Followup_Model_Rule::TYPE_CANCEL_CUSTOMER_CLICKLINK => $this->__('Customer clicked on a link in the email '),
            Amasty_Followup_Model_Rule::TYPE_CANCEL_CUSTOMER_WISHLIST_SHARED => $this->__('Customer wishlist shared'),
        ), $otherEvents);
    }
    
    public function getCancelReasons(){
        return array(
            
            Amasty_Followup_Model_History::REASON_BLACKLIST => $this->__('Black List'),
            Amasty_Followup_Model_History::REASON_EVENT => $this->__('Stop Event'),
            Amasty_Followup_Model_History::REASON_ADMIN => $this->__('Removed by Admin'),
            Amasty_Followup_Model_History::REASON_NOT_SUBSCRIBED => $this->__('Customer not subscribed'),
        );
    }
    
    public function getCustomerGroups(){
        $attribute = Mage::getModel('eav/entity_attribute')->loadByCode("customer", "group_id");
        return $attribute->getSource()->getAllOptions(false, true);
    }
    
    public function getSegmentsOptions(){
        $ret = array();
        
        foreach(Mage::getModel("amsegments/segment")
                ->getCollection()
                ->filterByStatus()
                    as $segment){
            $ret[] = array(
                "value" => $segment->getId(),
                "label" => $segment->getName(),
            );
        }
        return $ret;
    }
}