<?php

class MagentoMasters_MasterCheckout_Model_Observer
{
    public function saveCityAndSubdistrictName($observer)
    {
        $store_id = $observer->getEvent()->getOrder()->getStoreId();
        $orderId = $observer->getEvent()->getOrder()->getId();
        $orderAddresss = Mage::getModel('sales/order_address')->load($orderId,'parent_id');
        $cityId = $orderAddresss->getCityId();
        $cityEn = Mage::getModel('customaddress/city')->load($cityId)->getCode();
        $cityTh = Mage::getModel('customaddress/city')->load($cityId)->getDefaultName();
        $subdistrictId = $orderAddresss->getSubdistrictId();
        $subdistrictEn = Mage::getModel('customaddress/subdistrict')->load($subdistrictId)->getCode();
        $subdistrictTh = Mage::getModel('customaddress/subdistrict')->load($subdistrictId)->getDefaultName();
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        // store EN
        if ($store_id == '1' || $store_id == '6') {
            $query = "UPDATE sales_flat_order_address SET city = '".$cityEn."', subdistrict = '".$subdistrictEn."' WHERE parent_id = $orderId";
        }
        // store TH
        elseif ($store_id == '4' || $store_id == '5') {
            $query = "UPDATE sales_flat_order_address SET city = '".$cityTh."', subdistrict = '".$subdistrictTh."' WHERE parent_id = $orderId";
        }
        // default
        else{
            $query = "UPDATE sales_flat_order_address SET city = '".$cityTh."', subdistrict = '".$subdistrictTh."' WHERE parent_id = $orderId";
        }
        $writeConnection->query($query);
    }
}
