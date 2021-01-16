<?php
/**
 * Appmerce - Applications for Ecommerce
 * http://www.appmerce.com
 *
 * @extension   Auto-Cancel Orders
 * @type        Order management
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category	Magento Commerce
 * @package     Appmerce_AutoCancel
 * @copyright   Copyright (c) 2011-2013 Appmerce (http://www.appmerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Appmerce_AutoCancel_Model_Cron extends Mage_Core_Helper_Abstract
{
    protected $_divider = '@';

    /**
     * Cron check orders to be canceled
     */
    public function autoCancel($shedule = null)
    {
        $mappings = unserialize(Mage::getStoreConfig('autocancel/settings/mapping'));
        if (!$mappings || !is_array($mappings)) {
            return $this;
        }

        // Get relevant methods
        $methods = array();
        foreach ($mappings as $mapping_status => $period) {
            $map = explode($this->_divider, $mapping_status);
            if (isset($map[0]) && $map[0] && $map[1]) {
                $methods[] = $map[0];
            }
        }
        if (!$methods) {
            return $this;
        }
        $methods = ' IN ("' . implode('", "', $methods) . '")';

        // Database preparations
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $orderTable = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
        $orderPaymentTable = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_payment');

        // Time preparations
        // @note the from time has 25h, to include the last 24h old orders
        $gmtStamp = Mage::getModel('core/date')->gmtTimestamp();
        $from = date('Y-m-d H:i:s', $gmtStamp - (72 * 60 * 60));
        $to = date('Y-m-d H:i:s', $gmtStamp - (15 * 60));

        // Find orders that can be canceled, within last
        $timestamp_column = Mage::getStoreConfig('autocancel/settings/timestamp');

        Mage::log('SELECT sfo.increment_id, sfo.status, sfo.' . $timestamp_column . ' AS timestamp_column, sfop.method 
            FROM ' . $orderTable . ' sfo 
            INNER JOIN ' . $orderPaymentTable . ' sfop 
            ON sfop.parent_id = sfo.entity_id 
            WHERE sfo.state IN ("' . Mage_Sales_Model_Order::STATE_NEW . '", "' . Mage_Sales_Model_Order::STATE_PENDING_PAYMENT . '", "' . Mage_Sales_Model_Order::STATE_PROCESSING . '")
            AND sfo.' . $timestamp_column . ' >= "' . $from . '"
            AND sfo.' . $timestamp_column . ' <= "' . $to . '"
            AND sfop.method' . $methods);

        $result = $db->query('SELECT sfo.increment_id, sfo.status, sfo.' . $timestamp_column . ' AS timestamp_column, sfop.method 
            FROM ' . $orderTable . ' sfo 
            INNER JOIN ' . $orderPaymentTable . ' sfop 
            ON sfop.parent_id = sfo.entity_id 
            WHERE sfo.state IN ("' . Mage_Sales_Model_Order::STATE_NEW . '", "' . Mage_Sales_Model_Order::STATE_PENDING_PAYMENT . '", "' . Mage_Sales_Model_Order::STATE_PROCESSING . '")
            AND sfo.' . $timestamp_column . ' >= "' . $from . '"
            AND sfo.' . $timestamp_column . ' <= "' . $to . '"
            AND sfop.method' . $methods);

        if (!$result) {
            return $this;
        }

        // Cancel orders appropriately
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            if (!$row) {
                break;
            }

            // Check if this order needs auto-canceling
            $method_status = $row['method'] . $this->_divider . $row['status'];
            if (isset($mappings[$method_status]) && !empty($mappings[$method_status])) {

                // Check if auto-cancel time-limit fits this order
                $time_limit = date('Y-m-d H:i:s', $gmtStamp - ($mappings[$method_status] * 60));
                if ($row['timestamp_column'] <= $time_limit) {

                    // Load & cancel order
                    $order = Mage::getModel('sales/order')->loadByIncrementId($row['increment_id']);
                    if ($order->getId() && !$order->hasInvoices()) {
                        $this->cancel($order);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Cancel process
     *
     * Update failed, cancelled, declined, rejected etc. orders. Cancel
     * the order and show user message. Restore quote.
     *
     * @param $order object Mage_Sales_Model_Order
     */
    public function cancel(Mage_Sales_Model_Order $order)
    {
        if ($this->check($order)) {
            $note = Mage::helper('autocancel')->__('Automatically canceled after payment window expired.');
            $order->addStatusToHistory($order->getStatus(), $note, $notified = true);
            $order->cancel();
            $cancelStatus = Mage::getStoreConfig('autocancel/settings/cancel_status', $order->getStoreId());
            if ($cancelStatus) {
                $order->setStatus($cancelStatus);
            }
            $order->save();
        }
    }

    /**
     * Check order state
     *
     * If the order state (not status) is already one of:
     * canceled, closed, holded or completed,
     * then we do not update the order status anymore.
     *
     * @param $order object Mage_Sales_Model_Order
     */
    public function check(Mage_Sales_Model_Order $order)
    {
        $state = $order->getState();
        switch ($state) {
            case Mage_Sales_Model_Order::STATE_HOLDED :
            case Mage_Sales_Model_Order::STATE_CANCELED :
            case Mage_Sales_Model_Order::STATE_CLOSED :
            case Mage_Sales_Model_Order::STATE_COMPLETE :
                return false;
                break;

            default :
                return true;
        }
    }

}
