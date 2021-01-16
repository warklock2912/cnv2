<?php

class Plumrocket_Reservationaftercheckout_Helper_Data extends Plumrocket_Reservationaftercheckout_Helper_Main
{
    private $_configPrefix = 'reservation_after_checkout/';
    
    public function moduleEnabled($storeId = null)
    {
        return Mage::getStoreConfig($this->_configPrefix.'general/enable', $storeId);
    }

    public function getTime()
    {
        $timesArr = explode(',', Mage::getStoreConfig('reservation_after_checkout/general/time'));
        return (int)$timesArr[0] * 86400 + (int)$timesArr[1] * 3600 + (int)$timesArr[2] * 60 + (int)$timesArr[3];
    }

    public function getOlderOrders()
    {
        return $this->_getOrders('to');
    }

    public function getOrders()
    {
        return $this->_getOrders('from');
    }

    private function _getOrders($type)
    {
        return Mage::getModel('sales/order')
            ->getCollection()
            ->addFieldToFilter(
                'created_at', array(
                $type => strftime('%F %T', time() - $this->getTime()),
                'date' => true,
                )
            )
            ->addFieldToFilter(
                'status', array(
                'neq' => Mage_Sales_Model_Order::STATE_COMPLETE
                )
            )
            ->addFieldToFilter(
                'status', array(
                'neq' => Mage_Sales_Model_Order::STATE_CLOSED
                )
            )
            ->addFieldToFilter(
                'status', array(
                'neq' => Mage_Sales_Model_Order::STATE_CANCELED
                )
            );
    }


}