<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

    class Amasty_Followup_Model_Event_Order_Invoice extends Amasty_Followup_Model_Event_Order_Status
    {
        protected $_statusKey = 'invoice';
        
        protected function _getHistoryEntityName(){
            return Mage_Sales_Model_Order_Invoice::HISTORY_ENTITY_NAME;
        }
    }
?>