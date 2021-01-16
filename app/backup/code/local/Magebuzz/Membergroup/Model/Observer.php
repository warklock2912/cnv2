<?php
/**
 * Created by PhpStorm.
 * User: tungd
 * Date: 9/29/16
 * Time: 11:27 PM
 */

class Magebuzz_Membergroup_Model_Observer
{
  public function changeGroupCustomer($observer){
    $custmer = $observer->getCustomer();
    $vipstatus = $custmer->getData('vip_member_status');
    if($vipstatus == '2'){
      $custmer->setGroupId(4);
      $custmer->save();
    }
  }

  public function checkTotalOrderCustomer($observer){
    $order = $observer['order'];
    if ($order->getCustomerIsGuest() || !$order->getCustomerId()) {
      return $this;
    }
    $customerId = $order->getCustomerId();
    $customer = Mage::getModel('customer/customer')->load($customerId);
    if($customer->getData('vip_member_status') == '2'){
      return $this;
    }
    if ($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE){
      $minimumnumber = Mage::helper('membergroup')->getMinimumNumber();
      $collection = Mage::getResourceModel('sales/order_collection')
        ->addFieldToFilter('customer_id', $customerId)
        ->addFieldToFilter('status', 'complete');
      $collection->getSelect()->columns('SUM(base_grand_total) as sum_grand_total');
      $collection->getFirstItem();
      $sumTotal = $collection->getData('sum_grand_total');
      if($sumTotal >= $minimumnumber){
        $customer->setData('vip_member_status', '1');
        $customer->save();
      }
    }
  }
}