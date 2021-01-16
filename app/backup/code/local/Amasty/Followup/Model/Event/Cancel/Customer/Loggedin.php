<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

    class Amasty_Followup_Model_Event_Cancel_Customer_Loggedin extends Amasty_Followup_Model_Event_Basic
    {
        function validate($history){
            $customer = Mage::getModel('customer/customer')->load($history->getCustomerId());

            $logCustomer = null;

            if (version_compare(Mage::getVersion(), '1.5', '>')) {
                $logCustomer = Mage::getModel('log/customer')->loadByCustomer($customer);
            } else {
                $logCustomer = Mage::getModel('log/customer')->load($customer->getId());
            }
            
            return strtotime($logCustomer->getLoginAt()) > strtotime($history->getCreatedAt());
            
        }
    }
?>