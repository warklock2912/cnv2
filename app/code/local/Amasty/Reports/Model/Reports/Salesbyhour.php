<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Model_Reports_Salesbyhour extends Amasty_Reports_Model_Reports_Filters_Collection
{

    protected $_allowedFilters = array(
        'OrderStatus',
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
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $this->_addGroupOrder($ordersCollection);
        $select = $ordersCollection->getSelect();
        $results = $readConnection->fetchAll( $select );
        $results = $this->fillEmptyHour($results);
        return $results;
    }

    protected function _addGroupOrder($ordersCollection)
    {
        switch ($this->_orderBy) {
            case 'created_at':
                $ordersCollection->getSelect()->group( 'period' );
                break;
            case 'updated_at':
                $ordersCollection->getSelect()->group( 'period' );
                break;
        }
        $ordersCollection->getSelect()->reset(Zend_Db_Select::ORDER);
        $ordersCollection->getSelect()->order( 'period'  );
        return $ordersCollection;
    }

    public function getReportFields()
    {
        return array('ReportName','OrderStatus','OrdersBy','DateFrom', 'DateTo', 'Compare','StoreSelect');
    }

    protected function fillEmptyHour($results)
    {
        if (!$results) return $results;

        $diff = array();
        $allHours = range(0,23);
        foreach($results as $arr) {
            $diff[] = $arr['period'];
        }
        $diff = array_diff($allHours,$diff);
        foreach ($diff as $arr) {
            foreach ($results[0] as $attrKey => $attr) {
                if ($attrKey === 'period') {
                    $inserted[$attrKey] = $arr;
                } else {
                    $inserted[$attrKey] = 0;
                }
            }
            array_splice( $results, $arr, 0, array($inserted) );
        }
        return $results;
    }

    protected function _getSpecialPeriod($filters)
    {
        $offset = $this->_getOffset($filters['DateFrom']);
        $oper = $this->_getOper($offset);
        $offset = abs($offset);
        switch ($filters['OrdersBy']) {
            case 'created_at':
                $period[] = 'HOUR('.$oper.'(main_table.created_at, INTERVAL '.$offset.' SECOND)) as period';
                break;
            case 'updated_at':
                $period[] = 'HOUR('.$oper.'(main_table.updated_at, INTERVAL '.$offset.' SECOND)) as period';
                break;
        }
        return $period;
    }
}