<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Model_Reports_Profit extends Amasty_Reports_Model_Reports_Filters_Collection
{
    protected $_allowedFilters = array(
        'OrderStatus',
        'Period',
        'DateFrom',
        'DateTo',
    );

    protected function _getSelectedFields($filters)
    {
        $period = $this->_getPeriod($filters);
        $defFilters = array('COUNT(*) as count',
                     'SUM(main_table.`total_item_count`) as total_item_count',
                     'SUM(main_table.`base_grand_total`) as base_grand_total',
                     'SUM(main_table.`base_tax_amount`) as base_tax_amount',
                     'SUM(main_table.`base_shipping_amount`) as base_shipping_amount',
                     'SUM(main_table.`total_qty_ordered`) as total_qty_ordered',
                     'SUM(main_table.`base_discount_amount`) as base_discount_amount',
                     'SUM(main_table.`base_total_refunded`) as base_total_refunded',
                     //'SUM( order_item.base_cost ) as base_cost'
        );

        return array_merge($period,$defFilters);
    }

    public function getReport($filters)
    {
        $filters = $this->_prepareFields($filters);
        $ordersCollection = Mage::getModel('sales/order')->getCollection();
        /*
        $ordersCollection->getSelect()->join(
            array('order_item'=> Mage::getSingleton('core/resource')->getTableName('sales/order_item')),
            'order_item.order_id = main_table.entity_id'
        );*/
        $this->_addOrdersBy($ordersCollection,$filters['OrdersBy']);
        $selectedFields = $this->_getSelectedFields($filters);
        $ordersCollection = $this->_addFieldsToSelect($ordersCollection,$selectedFields);
        $ordersCollection = $this->_addFormulaToSelect($ordersCollection,$filters);
        $ordersCollection = $this->_applyFilters($ordersCollection,$filters);
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $select = $ordersCollection->getSelect();
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

    public function getReportFields()
    {
        return array('ReportName','OrderStatus','OrdersBy','DateFrom', 'DateTo', 'Compare','ShowEmpty','Period','StoreSelect','ProfitFormula');
    }

    public function getFormulaFields()
    {
        $hlp = Mage::helper('amreports');
        return array(
            '0' => $hlp->__('base_grand_total'),
            '1' => $hlp->__('base_tax_amount'),
            '2' => $hlp->__('base_shipping_amount'),
            '3' => $hlp->__('base_discount_amount'),
            '4' => $hlp->__('base_total_refunded')
        );
    }

    protected function getFormulaSql($index)
    {
        $actions = array(
            '0' => 'IFNULL(SUM(main_table.base_grand_total),0)',
            '1' => 'IFNULL(SUM(main_table.base_tax_amount),0)',
            '2' => 'IFNULL(SUM(main_table.base_shipping_amount),0)',
            '3' => 'IFNULL(SUM(main_table.base_discount_amount),0)',
            '4' => 'IFNULL(SUM(main_table.base_total_refunded),0)',
            '+' => '+',
            '-' => '-',
            '/' => '/',
            '*' => '*',
        );
        return $actions[$index];
    }

    protected function _addFormulaToSelect($ordersCollection,$filters)
    {
        $actionActions = array('+','-','/','*');
        $fieldList = $this->getFormulaFields();
        $formulaFields = $filters['ProfitFormula'];
        if (isset($formulaFields) &&
            ( array_intersect($formulaFields,$actionActions) || array_intersect($formulaFields,$fieldList) )) {
            $formula = '';
            foreach ($formulaFields as $field) {
                $formula .= $this->getFormulaSql($field);
            }
            $formula .= ' as profit';

            $ordersCollection->getSelect()
                ->columns(new Zend_Db_Expr($formula));
        }
        return $ordersCollection;
    }
}