<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

    class Amasty_Followup_Model_Event_Cancel_Customer_Wishlist_Shared extends Amasty_Followup_Model_Event_Basic
    {
        function validate($history){
            $collection = Mage::getModel("wishlist/wishlist")->getCollection()
                    ->addFieldToFilter('customer_id', array('eq' => $history->getCustomerId()))
                    ->addFieldToFilter('shared', array('eq' => 1))
                    ->addFieldToFilter('updated_at', array('gt' => $history->getCreatedAt()));
            
            return $collection->getSize() > 0;
        }
    }
?>