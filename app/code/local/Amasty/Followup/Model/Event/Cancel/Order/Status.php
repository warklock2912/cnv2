<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

    class Amasty_Followup_Model_Event_Cancel_Order_Status extends Amasty_Followup_Model_Event_Basic
    {
        protected $_status;
        public function __construct($rule, $status)
        {
            $this->_status = $status;
            return parent::__construct($rule);
        }
        
        function validate($history){
            
            $collection = Mage::getModel("sales/order_status_history")->getCollection()
                ->addFieldToFilter('created_at', array('gteq' => $history->getCreatedAt()))
                ->addFieldToFilter('parent_id', array('eq' => $history->getOrderId()))
                ->addFieldToFilter('status', array('eq' => $this->_status->getStatus()));
            
            return $collection->getSize() > 0;
        }
    }
?>