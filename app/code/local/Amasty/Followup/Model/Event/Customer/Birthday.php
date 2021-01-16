<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

    class Amasty_Followup_Model_Event_Customer_Birthday extends Amasty_Followup_Model_Event_Basic
    {
        function validate($customer){
            return $this->_validateBasic($customer->getStoreId(), $customer->getEmail(), $customer->getGroupId());
        }
        
        protected function _initCollection(){
        
            $resource = Mage::getSingleton('core/resource');
            
            $collection = Mage::getModel('customer/customer')->getCollection();
            
            $collection->addNameToSelect();
            
            $collection->getSelect()->joinLeft(
                array('history' => $resource->getTableName('amfollowup/history')), 
                'e.entity_id = history.customer_id and '.
                'history.rule_id = ' . $this->_rule->getId() . ' and '.
                'DATEDIFF(history.created_at, "' . $this->date($this->getCurrentExecution()) . '") = 0', 
                array()
            );
                    
            $collection->addExpressionAttributeToSelect('birth_month', 'MONTH({{dob}})', 'dob')
                        ->addExpressionAttributeToSelect('birth_day', 'DAY({{dob}})', 'dob')
                        ->joinAttribute('dob', 'customer/dob', 'entity_id', null, 'left')
                        ->addFieldToFilter('birth_month', array('eq' => 
                            date("m", $this->getCurrentExecution()
                        )))
                        ->addFieldToFilter('birth_day', array('eq' => 
                            date("d", $this->getCurrentExecution()
                        )));
            
            $collection->getSelect()->where("history.history_id is null");
            
            return $collection;
        }
    }
?>