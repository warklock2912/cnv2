<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

    class Amasty_Followup_Model_Event_Order_Status extends Amasty_Followup_Model_Event_Basic
    {
        protected $_statusKey = null;
        
        function validate($quote){
            return $this->_validateBasic($quote->getStoreId(), $quote->getCustomerEmail(), $quote->getCustomerGroupId()) &&
                    $this->_rule->validateAddress($quote);
        }
                
        protected function _initCollection(){
            $resource = Mage::getSingleton('core/resource');
            
            $collection = Mage::getModel('sales/quote')
                    ->getCollection();
            
            $collection->getSelect()->joinInner(
                array('order' => $resource->getTableName('sales/order')), 
                'main_table.entity_id = order.quote_id', 
                array(
                    'order_id' => 'order.entity_id',
                    'increment_id' => 'order.increment_id'
                    )
            );

            $collection->getSelect()->joinInner(
                array('order_history' => $resource->getTableName('sales/order_status_history')), 
                'order.entity_id = order_history.parent_id', 
                array('order_history.created_at as target_created_at')
            );

            $collection->addFieldToFilter('order_history.created_at', array(
                'gteq' => $this->date($this->getLastExecuted()))
            );
            
            $collection->addFieldToFilter('order_history.created_at', array(
                'lt' => $this->date($this->getCurrentExecution())
            ));

            if (version_compare(Mage::getVersion(), '1.5', '>')){
                $historyEntityName = $this->_getHistoryEntityName();

                if ($historyEntityName){
                    $collection->addFieldToFilter('order_history.entity_name', array(
                        'eq' => $historyEntityName
                    ));
                }
            }

            $collection->addFieldToFilter('order_history.status', array(
                'eq' => $this->_getOrderStatus()
            ));
            
            $collection->getSelect()->group("main_table.entity_id");
//            echo $collection->getSelect()."<br/><br/>";
            
            return $collection;
        }
        
        protected function _getHistoryEntityName(){
            return null;
        }
        
        protected function _getOrderStatus(){
            $ret = null;
            
            if ($this->_statusKey){
                $ret = Mage::getStoreConfig('amfollowup/statuses/' . $this->_statusKey);
            }
            
            return $ret;
        }
        
    }
?>