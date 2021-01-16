<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

    class Amasty_Followup_Model_Event_Customer_Wishlist extends Amasty_Followup_Model_Event_Basic
    {
        function validate($customer){
            $wishlist = Mage::getModel("wishlist/wishlist")->loadByCustomer($customer);

            $resource = Mage::getSingleton('core/resource');
            $frequencyUpdate = 60; // 1 hour

            $collection = Mage::getModel("amfollowup/history")->getCollection();

            $collection->getSelect()->join(
                array('wishlist' => $resource->getTableName('wishlist/wishlist')),
                'main_table.customer_id = wishlist.customer_id and '.
                'main_table.status = "' . Amasty_Followup_Model_History::STATUS_PENDING . '" and '.
                'main_table.rule_id = ' . $this->_rule->getId() . ' and '.
                'main_table.created_at > "' . $this->date((int)$this->getCurrentExecution() - $frequencyUpdate) . '"',
                array()
            );

            $collection->getSelect()->where("main_table.history_id is not null");

            if ($collection->getSize()) {
                foreach ($collection as $history) {
                    $history->setStatus(Amasty_Followup_Model_History::STATUS_CANCEL);
                    $history->save();
                }
            }


            return $wishlist->getItemsCount() > 0 &&
                    $this->_validateBasic($customer->getStoreId(), $customer->getEmail(), $customer->getGroupId());
        }
        
        protected function _initCollection(){
            $resource = Mage::getSingleton('core/resource');
            
            $collection = Mage::getModel('customer/customer')->getCollection();
            
            $collection->addNameToSelect();
            
            $collection->getSelect()->joinInner(
                array('wishlist' => $resource->getTableName('wishlist/wishlist')), 
                'e.entity_id = wishlist.customer_id',
                array()
            );

            $collection->getSelect()->where("wishlist.updated_at > '" . $this->date($this->getLastExecuted()) . "'");
            $collection->getSelect()->where("wishlist.updated_at < '" . $this->date($this->getCurrentExecution()) . "'");

            $collection->getSelect()->group("e.entity_id");

            return $collection;
        }
    }
?>