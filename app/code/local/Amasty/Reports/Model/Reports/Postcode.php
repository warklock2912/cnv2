<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Model_Reports_Postcode extends Amasty_Reports_Model_Reports_Filters_Collection
{
    protected $_allowedFilters = array(
        'OrderStatus',
        'Period',
        'DateFrom',
        'DateTo',
        'Sku',
    );

    protected function _getSelectedFields($filters)
    {
        $period[] = 'quote_address.postcode as period';
        $defFilters = array(
            'COUNT(*) as count',
            'SUM(order.`total_item_count`) as total_item_count', //item count
            'SUM(order.`total_qty_ordered`) as total_qty_ordered', //total qty in cart
            'SUM(order.`base_grand_total`) as base_grand_total',
            'SUM(order.`base_tax_amount`) as base_tax_amount',
            'SUM(order.`base_shipping_amount`) as base_shipping_amount',
            'SUM(order.`base_discount_amount`) as base_discount_amount',
            'SUM(order.`base_subtotal`) + SUM(order.`base_tax_amount`) + SUM(order.`base_shipping_amount`) + SUM(order.`base_discount_amount`) as total_sum',
            'SUM(order.`base_total_invoiced`) as base_total_invoiced',

        );
        return array_merge($period,$defFilters);
    }

    public function getReport($filters)
    {
        $ordersCollection = Mage::getModel('sales/order')->getCollection();
        $ordersCollection->getSelect()->join(
            array('order'=> Mage::getSingleton('core/resource')->getTableName('sales/order')),
            'order.quote_id = main_table.quote_id'
        );
        $selectedFields = $this->_getSelectedFields($filters);
        $ordersCollection = $this->_addFieldsToSelect($ordersCollection,$selectedFields);
        $this->_addOrdersBy($ordersCollection,$filters['OrdersBy']);
        $filters = $this->_prepareFields($filters);
        $ordersCollection = $this->_applyFilters($ordersCollection,$filters);
        $ordersCollection = $this->_addReportStatement($ordersCollection);
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
        return array('ReportName','OrderStatus','SelectOrdersBy','DateFrom', 'DateTo', 'Period','StoreSelect');
    }

    protected function _addReportStatement($ordersCollection)
    {
        $ordersCollection->getSelect()->group( 'quote_address.postcode'  );
        $ordersCollection->getSelect()->where( 'quote_address.postcode IS NOT NULL'  );
        return $ordersCollection;
    }
}