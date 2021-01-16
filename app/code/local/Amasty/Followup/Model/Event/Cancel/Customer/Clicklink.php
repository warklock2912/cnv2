<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

    class Amasty_Followup_Model_Event_Cancel_Customer_Clicklink extends Amasty_Followup_Model_Event_Basic
    {
        function validate($history){
            $collectionLink = Mage::getModel("amfollowup/link")->getCollection()
                    ->addFieldToFilter('customer_id', array('eq' => $history->getCustomerId()))
                    ->addFieldToFilter('created_at', array('gt' => $history->getCreatedAt()));
            
            $collectionHistoryLink = Mage::getModel("amfollowup/link")->getCollection()
                    ->addHistoryData()
                    ->addFieldToFilter('schedule_id', array('eq' => $history->getScheduleId()))
                    ->addFieldToFilter('main_table.customer_id', array('null' => true))
                    ->addFieldToFilter('main_table.created_at', array('gt' => $history->getCreatedAt()));
            
            return $collectionLink->getSize() > 0 || $collectionHistoryLink->getSize() > 0;
        }
    }
?>