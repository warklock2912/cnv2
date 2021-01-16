<?php
class Plumrocket_Reservationaftercheckout_Model_Cron
{
    
    public function checkOrders()
    {
        if (!Mage::helper('reservationaftercheckout')->moduleEnabled()){
            return $this;
        }

        $orders = Mage::helper('reservationaftercheckout')->getOlderOrders();
        foreach ($orders as $order) {
            if ($order->canCancel()) {
                $order->cancel()->save();
            }
        }
    }
}