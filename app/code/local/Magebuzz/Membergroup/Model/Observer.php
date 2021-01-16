<?php

/**
 * Class Magebuzz_Membergroup_Model_Observer
 */
class Magebuzz_Membergroup_Model_Observer
{
    /**
     * @param $observer
     * @throws Exception
     */
    public function changeGroupCustomer($observer)
    {
        $dateNow = new DateTime("now", new DateTimeZone('Asia/Bangkok'));
        $now = $dateNow->format('Y-m-d');

        $newExpireDate = date('Y-m-d', strtotime($now . ' ' . Mage::getStoreConfig('membergroup/general/expire_days') . ' day'));

        /** @var Mage_Customer_Model_Customer $customer * */
        $customer = $observer->getCustomer();

        if ($customer->getData('is_update_vip')) {
            if ($customer->getData('group_id') == Mage::getStoreConfig('membergroup/general/vip_member_id')) {
                $customer->setData('updated_group_at', Mage::getModel('core/date')->gmtDate('Y-m-d'))
                    ->setData('vip_member_expire_date', $newExpireDate);
                $customer->save();
            }
        } else {
            if ($customer->getData('vip_member_status') == '2')
                $customer->setData('group_id', Mage::getStoreConfig('membergroup/general/vip_member_id'));
            if ($customer->getData('group_id') == Mage::getStoreConfig('membergroup/general/vip_member_id')) {
                if (!$customer->getData('vip_member_expire_date'))
                    $customer->setData('vip_member_expire_date', $newExpireDate);
                if (!$customer->getData('updated_group_at'))
                    $customer->setData('updated_group_at', Mage::getModel('core/date')->gmtDate('Y-m-d'));
            }
            $customer->save();
        }
    }

    /**
     * @param $observer
     * @return $this
     * @throws Exception
     */
    public function checkTotalOrderCustomer($observer)
    {
        $order = $observer['order'];
        if ($order->getCustomerIsGuest() || !$order->getCustomerId()) {
            return $this;
        }
        $customerId = $order->getCustomerId();

        /** @var Mage_Customer_Model_Customer $customer * */
        $customer = Mage::getModel('customer/customer')->load($customerId);

        $dateNow = new DateTime("now", new DateTimeZone('Asia/Bangkok'));
        $now = $dateNow->format('Y-m-d');

        if ($customer->getData('vip_member_status') == '2') {
            return $this;
        }
        if ($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE) {
            $minimumnumber = Mage::helper('membergroup')->getMinimumNumber();
//            $collection = Mage::getResourceModel('sales/order_collection')
//                ->addFieldToFilter('customer_id', $customerId)
//                ->addFieldToFilter('status', 'complete');
//            $collection->getSelect()->columns('SUM(base_grand_total) as sum_grand_total');
//            $collection->getFirstItem();
//            $sumTotal = $collection->getData('sum_grand_total');
            $sumTotal = $order->getGrandTotal();
            if ($customer->getGroupId() != Mage::getStoreConfig('membergroup/general/vip_member_id')) {
                $newExpireDate = date('Y-m-d', strtotime($now . ' ' . Mage::getStoreConfig('membergroup/general/expire_days') . ' day'));
                if ($sumTotal >= $minimumnumber) {
                $customer->setData('group_id', Mage::getStoreConfig('membergroup/general/vip_member_id'))
                    ->setData('updated_group_at', Mage::getModel('core/date')->gmtDate('Y-m-d'))
                    ->setData('vip_member_expire_date', $newExpireDate)
                    ->setData('vip_member_status', 2)
                    ->setData('is_update_vip', true);
                }
            }
            else {
                $dateToTime = new DateTime($customer->getData('vip_member_expire_date'), new DateTimeZone('Asia/Bangkok'));
                $toTime = $dateToTime->format('Y-m-d');
                $duringTime = date('Y-m-d', strtotime($toTime . ' -' . Mage::getStoreConfig('membergroup/general/before_expire_days') . ' day'));
                if ($now >= $duringTime && $now <= $toTime) {
                    if ($sumTotal >= (float)Mage::getStoreConfig('membergroup/general/before_expire_days_grand_total')) {
                        $customer->setData('group_id', Mage::getStoreConfig('membergroup/general/vip_member_id'))
                            ->setData('is_update_vip', true);
                        $customer->save();
                    }
                }
            }
            $customer->save();
        }
    }
}