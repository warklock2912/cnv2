<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

    class Amasty_Followup_Model_Event_Cancel_Order_Complete extends Amasty_Followup_Model_Event_Basic
    {
        function validate($history){
            
            $collection = Mage::getModel("sales/order")->getCollection();
            
            if ($history->getOrderId()) {
                $collection->addFieldToFilter('entity_id', array('gt' => $history->getOrderId()));
            } else {
                $collection->addFieldToFilter('created_at', array('gteq' => $history->getCreatedAt()));
            }
                    
            if ($history->getCustomerId()) {
                $collection->addFieldToFilter('customer_id', array('eq' => $history->getCustomerId()));
            } else {
                $collection->addFieldToFilter('customer_email', array('eq' => $history->getEmail()));
            }
            
            return $collection->getSize() > 0;
        }
    }
?>