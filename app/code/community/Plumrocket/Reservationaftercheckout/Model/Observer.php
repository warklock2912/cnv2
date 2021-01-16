<?php

class Plumrocket_Reservationaftercheckout_Model_Observer extends Mage_Core_Model_Abstract
{
    
    public function saveOrder($observer)
    {
        if (!Mage::helper('reservationaftercheckout')->moduleEnabled()){
            return $this;
        }

        $order = $observer->getEvent()->getOrder();

        // After invoice, cancel or close - un reserve it.
        if (($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE)
            || ($order->getState() == Mage_Sales_Model_Order::STATE_CANCELED)
            || ($order->getState() == Mage_Sales_Model_Order::STATE_CLOSED)
        ) {
            $this->_unreserveOrder($order);

        // New
        } elseif ($order->getState() == Mage_Sales_Model_Order::STATE_NEW) {
            $items = $order->getAllItems();

            foreach ($items as $item) {
                $pid = $item->getProductId();
                $qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($pid)->getQty();

                if ($qty == 0) {
                    $storeId=Mage::app()->getStore()->getStoreId();
                    $action = Mage::getModel('catalog/resource_product_action');
                    $action->updateAttributes(array($pid), array(
                        'reserved_after_order' => 1
                    ),$storeId);
                }
            }
        }
    }

    public function createInvoice($observer)
    {
        if (!Mage::helper('reservationaftercheckout')->moduleEnabled()){
            return $this;
        }

        $this->_unreserveOrder($observer->getEvent()->getOrder());
    }

    private function _unreserveOrder($order)
    {
        if (Mage::getSingleton('plumbase/observer')->customer() == Mage::getSingleton('plumbase/product')->currentCustomer()) {
            $pids = array();
            $items = $order->getAllItems();
            foreach ($items as $item) {
                $pids[ $item->getProductId() ] = 1;
            }

            // If canceled then all products will be have >= 1 item - because ignore the check.
            // For invoice - order state will be Processing or Complete - then code below should be executed.
            if ($order->getState() != Mage_Sales_Model_Order::STATE_CANCELED) {
                $orders = Mage::helper('reservationaftercheckout')->getOrders();
                foreach ($orders as $ord) {
                    // If after invoice creation (state Processing) then the orders array will contain current order.
                    if ($ord->getId() != $order->getId()) {
                        $items = $ord->getAllItems();
                        foreach ($items as $item) {
                            $pid = $item->getProductId();
                            if (isset($pids[ $pid ])) {
                                unset($pids[ $pid ]);

                                // if all products were excluded then break all foreachs
                                if (sizeof($pids) == 0) {
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }

            foreach ($pids as $pid => $_) {
                $storeId=Mage::app()->getStore()->getStoreId();
                $action = Mage::getModel('catalog/resource_product_action');
                $action->updateAttributes(array($pid), array(
                    'reserved_after_order' => 0
                ),$storeId);

            }
        }
    }
}
