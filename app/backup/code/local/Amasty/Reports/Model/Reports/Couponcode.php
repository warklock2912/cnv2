<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Model_Reports_Couponcode extends Amasty_Reports_Model_Reports_Filters_Collection
{
    protected $_allowedFilters = array(
        'OrderStatus',
        'DateFrom',
        'DateTo',
        'StoreSelect'
    );

    protected function _getSelectedFields($filters)
    {
        $period[] = 'main_table.coupon_code as coupon_code';
        $defFilters = array(
            'COUNT(*) as count',
            'SUM(main_table.`total_item_count`) as total_item_count', //item count
            'SUM(main_table.`total_qty_ordered`) as total_qty_ordered', //total qty in cart
            'SUM(main_table.`base_grand_total`) as base_grand_total',
            'SUM(main_table.`base_tax_amount`) as base_tax_amount',
            'SUM(main_table.`base_shipping_amount`) as base_shipping_amount',
            'SUM(main_table.`base_discount_amount`) as base_discount_amount',
            'SUM(main_table.`base_subtotal`) + SUM(main_table.`base_tax_amount`) + SUM(main_table.`base_shipping_amount`) + SUM(main_table.`base_discount_amount`) as total_sum',
            'SUM(main_table.`base_total_invoiced`) as base_total_invoiced',

        );
        return array_merge($period,$defFilters);
    }

    public function getReport($filters)
    {
        $ordersCollection = Mage::getModel('sales/order')->getCollection();
        $selectedFields = $this->_getSelectedFields($filters);
        $ordersCollection = $this->_addFieldsToSelect($ordersCollection,$selectedFields);
        $filters = $this->_prepareFields($filters);
        $this->_addOrdersBy($ordersCollection,$filters['OrdersBy']);
        $ordersCollection = $this->_applyFilters($ordersCollection,$filters);
        $ordersCollection = $this->_addCouponGroup($ordersCollection);
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $select = $ordersCollection->getSelect();
        $results = $readConnection->fetchAll( $select );
        return $results;
    }

    protected function _addSku($ordersCollection, $value)
    {
        $ordersCollection->addFieldToFilter( 'order_item.sku' , $value );
        return $ordersCollection;
    }

    public function getReportFields()
    {
        return array('ReportName','OrderStatus','OrdersBy','DateFrom', 'DateTo', 'StoreSelect');
    }

    protected function _addCouponGroup($ordersCollection)
    {
        $ordersCollection->getSelect()->group( 'main_table.coupon_code'  );
        $ordersCollection->getSelect()->where( 'main_table.coupon_code IS NOT NULL'  );
        return $ordersCollection;
    }
}