<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

    class Amasty_Followup_Model_Event_Customer_Subscription extends Amasty_Followup_Model_Event_Basic
    {
        function validateSubscription($subscriber, $customer){
            return $this->_validateBasic($customer->getStoreId(), $customer->getEmail(), $customer->getGroupId()) &&
                in_array($subscriber->getSubscriberStatus(), array(Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE, Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED));
        }
    }
?>