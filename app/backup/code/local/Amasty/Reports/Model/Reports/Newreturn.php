<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Model_Reports_Newreturn extends Amasty_Reports_Model_Reports_Filters_Select
{
    protected $_allowedFilters = array(
        'OrderStatus',
        'DateFrom',
        'DateTo',
    );

    protected function _getSelectedFields($filters)
    {
        $period = $this->_getPeriod($filters);
        $defFilters = array(
            'SUM(date(customer_entity.created_at)=date(main_table.created_at)) as newUser',
            'SUM(date(customer_entity.created_at)!=date(main_table.created_at)) as returnUser',
            'SUM(IF(date(customer_entity.created_at)=date(main_table.created_at), main_table.total_invoiced, \'0\' )) as newPaid',
            'SUM(IF(date(customer_entity.created_at)!=date(main_table.created_at), main_table.total_invoiced, \'0\' )) as returnPaid'
        );
        return array_merge($period,$defFilters);
    }

    public function getReport($filters)
    {
        $filters = $this->_prepareFields($filters);
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_write');
        $select = $readConnection->select();
        $select
            ->from(
                array(
                    'main_table' => Mage::getSingleton('core/resource')->getTableName('sales/order'))
            )
            ->joinInner(
                array(
                    'customer_entity' => Mage::getSingleton('core/resource')->getTableName('customer/entity')),
                'main_table.customer_id = customer_entity.entity_id'
            );
        $select->group('period');
        $this->_addFieldsToSelect($select,$this->_getSelectedFields($filters));
        if (!isset($filters['StoreSelect'])) {
            $filters['StoreSelect'] = array(0);
        }
        $storeIds = implode(',', array_filter($filters['StoreSelect']));
        $this->_addStoreSelect('main_table',$select, $readConnection, $storeIds);
        $this->_applyFilters('main_table', $select, $readConnection, $filters);

        //var_dump((string)$select);

        $results = $readConnection->fetchAll( $select );
        return $results;
    }

    public function getReportFields()
    {
        return array('ReportName','DateFrom', 'DateTo', 'OrderStatus',  'StoreSelect');
    }

    protected function _getSpecialPeriod($filters)
    {
        $offset = $this->_getOffset($filters['DateFrom']);
        $oper = $this->_getOper($offset);
        $offset = abs($offset);
        $period[] = 'date('.$oper.'(main_table.created_at, INTERVAL '.$offset.' SECOND)) as period';
        return $period;
    }
}