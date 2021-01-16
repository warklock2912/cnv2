<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Model_Reports_Salesbyproduct extends Amasty_Reports_Model_Reports_Filters_Collection
{

    protected $_allowedFilters = array(
        'OrderStatus',
        'Period',
        'DateFrom',
        'DateTo',
        'Sku',
        'StoreSelect',
    );

    protected function _getSelectedFields($filters)
    {
        $period = $this->_getPeriod($filters);
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
        $filters = $this->_prepareFields($filters);
        $ordersCollection = Mage::getModel('sales/order')->getCollection();
        $this->_addOrdersBy($ordersCollection,$filters['OrdersBy']);
        $ordersCollection->getSelect()->join(
            array('order_item'=> Mage::getSingleton('core/resource')->getTableName('sales/order_item')),
            'order_item.order_id = main_table.entity_id'
        );
        $selectedFields = $this->_getSelectedFields($filters);
        $ordersCollection = $this->_addFieldsToSelect($ordersCollection,$selectedFields);

        $ordersCollection = $this->_applyFilters($ordersCollection,$filters);
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $select = $ordersCollection->getSelect();
/*
        var_dump((string)$select);
        exit;
*/
        $results = $readConnection->fetchAll( $select );
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

    protected function _addSku($ordersCollection, $value)
    {
        $ordersCollection->addFieldToFilter( 'order_item.sku' , $value );
        return $ordersCollection;
    }

    public function getReportFields()
    {
        return array('ReportName','OrderStatus','OrdersBy','DateFrom', 'DateTo', 'Compare', 'Sku', 'Period', 'ShowEmpty' ,'StoreSelect');
    }
}