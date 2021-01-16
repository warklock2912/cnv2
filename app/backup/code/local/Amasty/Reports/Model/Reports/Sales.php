<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Model_Reports_Sales extends Amasty_Reports_Model_Reports_Filters_Collection
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
        $this->_addOrdersBy($ordersCollection,$filters['OrdersBy']);
        $selectedFields = $this->_getSelectedFields($filters);
        $ordersCollection = $this->_addFieldsToSelect($ordersCollection,$selectedFields);
        $ordersCollection = $this->_applyFilters($ordersCollection,$filters);
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $select = $ordersCollection->getSelect();
        $results = $readConnection->fetchAll( $select );
        //$filters = $this->_fixTime($filters);
        if ($filters['ShowEmpty']==1) {
            switch ($filters['Period']){
                case 'TO_DAYS':
                    $results = $this->_addEmptyDayRows($results,
                        $filters['DateFrom'],
                        $filters['DateTo']);
                    break;
                case 'MONTH':
                    $results = $this->_addEmptyMonthRows($results,
                        $filters['DateFrom'],
                        $filters['DateTo']);
                    break;
                case 'YEAR':
                    $results = $this->_addEmptyYearRows($results,
                        $filters['DateFrom'],
                        $filters['DateTo']);
                    break;
            }
        }
        return $results;
    }

    public function getReportFields()
    {
        return array('ReportName','OrderStatus','OrdersBy','DateFrom', 'DateTo', 'Compare','ShowEmpty','Period','StoreSelect');
    }
}