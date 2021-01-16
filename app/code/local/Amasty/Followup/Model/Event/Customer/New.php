<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

    class Amasty_Followup_Model_Event_Customer_New extends Amasty_Followup_Model_Event_Basic
    {
        function validate($customer){
            return $this->_validateBasic($customer->getStoreId(), $customer->getEmail(), $customer->getGroupId()) &&
                    $customer->getOrigData() === NULL;
        }
    }
?>