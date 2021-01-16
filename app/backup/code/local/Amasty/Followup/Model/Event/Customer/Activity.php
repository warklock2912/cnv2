<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

    class Amasty_Followup_Model_Event_Customer_Activity extends Amasty_Followup_Model_Event_Basic
    {
        function validate($customer){
            return $this->_validateBasic($customer->getStoreId(), $customer->getEmail(), $customer->getGroupId());
        }
        
        protected function _initCollection(){
            $winbackPeriod = Mage::getStoreConfig('amfollowup/general/winback_period') * 60 * 60 * 24;
            $resource = Mage::getSingleton('core/resource');
            
            $collection = Mage::getModel('customer/customer')->getCollection();
            
            $collection->addNameToSelect();
            
            $collection->getSelect()->joinLeft(
                array('log' => $resource->getTableName('log/customer')), 
                'e.entity_id = log.customer_id',
                array()
            );
            
            $collection->getSelect()->joinLeft(
                array('history_n_canceled' => $resource->getTableName('amfollowup/history')), 
                'e.entity_id = history_n_canceled.customer_id and '.
                'history_n_canceled.rule_id = ' . $this->_rule->getId() . ' and ' .
                'history_n_canceled.status <> "' . Amasty_Followup_Model_History::STATUS_CANCEL . '"',
                array()
            );
            
            $collection->getSelect()->where("history_n_canceled.history_id is null");
            
            $collection->getSelect()->where("log.login_at > '".$this->date($this->getLastExecuted() - $winbackPeriod)."'");
            
            $collection->getSelect()->group("e.entity_id");
            
            $collection->getSelect()->having("MAX(log.login_at) < '" . $this->date((int)$this->getCurrentExecution() - $winbackPeriod) . "'");
            
            return $collection;
        }
    }
?>