<?php
class Tigren_Member_Model_Observer
{
    public function saveMissingOrder()
    {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');
        $sql        = "SELECT * FROM sales_flat_order WHERE sales_flat_order.entity_id not in (SELECT entity_id FROM sales_flat_order_grid)";
        $result       = $readConnection->fetchAll($sql);
        foreach ($result as $item) {
            $currentDate = Mage::getModel('core/date')->date('Y-m-d H:i:s');
            $order = Mage::getModel('sales/order')->load($item['entity_id']);
            $incrementId = $order->getIncrementId();
            $shippingName = $order->getShippingAddress()->getName();
            $billingName = $order->getBillingAddress()->getName();
            $value = array("entity_id" => $item['entity_id'],
                "status" => $item['status'],
                "store_id" => $item['store_id'],
                "store_name" => $item['store_name'],
                "customer_id" => $item['customer_id'],
                "base_grand_total" => $item['base_grand_total'],
                "base_total_paid" => $item['base_total_paid'],
                "grand_total" => $item['grand_total'],
                "total_paid" => $item['total_paid'],
                "increment_id" => $item['increment_id'],
                "base_currency_code" => $item['base_currency_code'],
                "order_currency_code" => $item['order_currency_code'],
                "shipping_name" => $shippingName,
                "billing_name" => $billingName,
                "created_at" => $item['created_at'],
                "updated_at" => $item['updated_at']);
            $writeConnection->insert("sales_flat_order_grid", $value);
            Mage::log($currentDate." re-save order ".$incrementId, null, 'missing_order.log');
        }
    }

    public function sendMailNotifyExpireDate()
    {
        $store = Mage::app()->getStore();
        $toDate = date('Y-m-d H:i:s', strtotime(now()));
        $customerCollection = Mage::getModel("customer/customer")->getCollection();
        $customerCollection->addAttributeToSelect('vip_member_expire_date');
        $customerCollection->addAttributeToFilter('vip_member_expire_date', array('notnull' => true));
        $customerCollection->addAttributeToFilter('vip_member_expire_date', array('gt' => $toDate));
        $customerCollection->addAttributeToSort('entity_id', 'DESC');
        $customerCollection->getSelect()->joinLeft(
            array('notify' => $customerCollection->getTable('member/notify_vip')),
            'notify.customer_id = e.entity_id',
            array('notified_member' => 'notify.notified_member')
        )->where('notified_member = 0');
        foreach($customerCollection as $customer)
        {
            $customerData = Mage::getModel('customer/customer')->load($customer->getId());
            if(!empty($customerData->getVipMemberExpireDate())) {
                $notifyRenew =  Mage::getStoreConfig('rewardpoints/display/expired_vip_after');
                $date = new DateTime("now", new DateTimeZone('Asia/Bangkok'));
                $dateExpired = new DateTime($customerData->getVipMemberExpireDate());
                $interval = $date->diff($dateExpired);
                $expiredAfter = $interval->days;
                $rewardAccount = Mage::getModel('rewardpoints/customer')->load($customerData->getId(), 'customer_id');
                if ($expiredAfter <= intval($notifyRenew)) {
                    $translate = Mage::getSingleton('core/translate');
                    $translate->setTranslateInline(false);
                    Mage::getModel('core/email_template')
                        ->setDesignConfig(array(
                            'area' => 'frontend',
                            'store' => $store->getId()
                        ))->sendTransactional(
                            'rewardpoints_email_notify_renew',
                            Mage::getStoreConfig('rewardpoints/email/sender', $store),
                            $customerData->getEmail(),
                            $customerData->getName(),
                            array(
                                'store' => $store,
                                'customer' => $customerData,
                                'total' => $rewardAccount->getPointBalance(),
                                'point_balance' => Mage::helper('rewardpoints/point')->format($rewardAccount->getPointBalance(), $store),
                                'expirationdays' => $expiredAfter,
                                'expirationdate' => date('M d, Y H:i:s',strtotime($customerData->getVipMemberExpireDate())),
                                'link' => '<a href="' .Mage::getStoreConfig('rewardpoints/display/frontend_link', $store). '">' .Mage::getStoreConfig('rewardpoints/display/frontend_link', $store). '</a>',
                            )
                        );
                    $translate->setTranslateInline(true);
                }
            }
        }
    }
}