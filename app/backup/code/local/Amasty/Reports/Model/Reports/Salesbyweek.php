<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Model_Reports_Salesbyweek extends Amasty_Reports_Model_Reports_Filters_Collection
{

    protected $_allowedFilters = array(
        'OrderStatus',
        'Period',
        'DateFrom',
        'DateTo',
        'StoreSelect'
    );

    protected function _getSelectedFields($filters)
    {
        $period = $this->_getPeriod($filters);
        $defFilters = array(
            'COUNT(*) as count',
            'SUM(`total_item_count`) as total_item_count', //item count
            'SUM(`total_qty_ordered`) as total_qty_ordered', //total qty in cart
            'SUM(`base_grand_total`) as base_grand_total',
            'SUM(`base_tax_amount`) as base_tax_amount',
            'SUM(`base_shipping_amount`) as base_shipping_amount',
            'SUM(`base_discount_amount`) as base_discount_amount',
            'SUM(`base_subtotal`) + SUM(`base_tax_amount`) + SUM(`base_shipping_amount`) + SUM(`base_discount_amount`) as total_sum',
            'SUM(`base_total_invoiced`) as base_total_invoiced',

        );
        return array_merge($period,$defFilters);
    }

    public function getReport($filters)
    {
        $filters = $this->_prepareFields($filters);
        $ordersCollection = Mage::getModel('sales/order')->getCollection();
        $selectedFields = $this->_getSelectedFields($filters);
        $ordersCollection = $this->_addFieldsToSelect($ordersCollection,$selectedFields);
        $this->_addOrdersBy($ordersCollection,$filters['OrdersBy']);
        $ordersCollection = $this->_applyFilters($ordersCollection,$filters);
        $this->_addGroupOrder($ordersCollection);
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $select = $ordersCollection->getSelect();
        $results = $readConnection->fetchAll( $select );
        return $results;
    }

    protected function _addGroupOrder($ordersCollection)
    {
        $ordersCollection->getSelect()->group( 'period' );
        switch ($this->_orderBy) {
            case 'created_at':
                $ordersCollection->getSelect()->order( 'period' );
                break;
            case 'updated_at':
                $ordersCollection->getSelect()->order( 'period' );
                break;
        }
        return $ordersCollection;
    }

    protected function _getSpecialPeriod($filters)
    {
        $offset = $this->_getOffset($filters['DateFrom']);
        $oper = $this->_getOper($offset);
        $offset = abs($offset);
        switch ($filters['OrdersBy']) {
            case 'created_at':
                $period[] = 'DAYNAME('.$oper.'(main_table.created_at, INTERVAL '.$offset.' SECOND)) as period';
                break;
            case 'updated_at':
                $period[] = 'DAYNAME('.$oper.'(main_table.updated_at, INTERVAL '.$offset.' SECOND)) as period';
                break;
        }
        return $period;
    }

    public function getReportFields()
    {
        return array('ReportName','OrderStatus','OrdersBy','DateFrom', 'DateTo', 'Compare','StoreSelect');
    }
}