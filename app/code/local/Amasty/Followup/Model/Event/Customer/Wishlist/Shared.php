<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

    class Amasty_Followup_Model_Event_Customer_Wishlist_Shared extends Amasty_Followup_Model_Event_Basic
    {
        function validate($customer){
            $wishlist = Mage::getModel("wishlist/wishlist")->loadByCustomer($customer);
            
            return $wishlist->getItemsCount() > 0 &&
                    $this->_validateBasic($customer->getStoreId(), $customer->getEmail(), $customer->getGroupId());
        }
        
    }
?>