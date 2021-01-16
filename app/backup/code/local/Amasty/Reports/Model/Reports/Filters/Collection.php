<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Model_Reports_Filters_Collection extends Amasty_Reports_Model_Reports_Abstract
{
    protected $_orderBy = '';

    protected function _addFieldsToSelect($ordersCollection, array $fields)
    {
        $ordersCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        foreach ($fields as $field) {
            $ordersCollection->getSelect()
                ->columns($field);
        }
        return $ordersCollection;
    }

    protected function _addDateTo($ordersCollection, $value)
    {
        if (empty($value)) {
            return $ordersCollection;
        }
        $value = $this->_phpDateToMysqlTo($value);
        $ordersCollection->addFieldToFilter( 'main_table.'.$this->_orderBy , array('lteq' => $value));
        return $ordersCollection;
    }

    protected function _addDateFrom($ordersCollection , $value)
    {
        if (empty($value)) {
            return $ordersCollection;
        }
        $value = $this->_phpDateToMysqlFrom($value);
        $ordersCollection->addFieldToFilter( 'main_table.'.$this->_orderBy , array('gteq' => $value));
        return $ordersCollection;
    }

    protected function _addStoreSelect($ordersCollection,  $value)
    {
        if (empty($value)) {
            return $ordersCollection;
        }
        $ordersCollection->addFieldToFilter('main_table.store_id', array( 'in'=>explode(',',$value) ) );
        return $ordersCollection;
    }

    protected function _addOrderStatus($ordersCollection,$value)
    {
        if (empty($value)) {
            return $ordersCollection;
        }
        $ordersCollection->addFieldToFilter('main_table.status', array( 'in'=>explode(',',$value) ) );
        return $ordersCollection;
    }

    protected function _addOrdersBy($ordersCollection, $value)
    {
        switch ($value) {
            case 'created_at':
                $ordersCollection->getSelect()->order('main_table.created_at');
                $this->_orderBy = 'created_at';
                break;
            case 'updated_at':
                $ordersCollection->getSelect()->order('main_table.updated_at');
                $this->_orderBy = 'updated_at';
                break;
        }
        return $ordersCollection;
    }

    protected function _addPeriod($ordersCollection, $value)
    {
        $ordersCollection->getSelect()->group('period');
        /*
        switch ($value) {
            case 'TO_DAYS':
                $ordersCollection->getSelect()->group('TO_DAYS(period)');
                break;
            case 'MONTH':
                $ordersCollection->getSelect()->group('MONTH(period)');
                break;
            case 'YEAR':
                $ordersCollection->getSelect()->group('YEAR(period)');
                break;
        }*/
        return $ordersCollection;
    }

    protected function _addInvoiced($ordersCollection , $value)
    {
        //$ordersCollection->addFieldToFilter( 'main_table.base_total_invoiced' , array('gteq' => $value));
        return $ordersCollection;
    }

    protected function _applyFilters($ordersCollection,$filters)
    {
        foreach ($filters as $name=>$filter){
            if ( in_array( $name,$this->_allowedFilters ) && !empty($filter) ) {
                $funcName = '_add' . ucfirst($name);
                $this->$funcName($ordersCollection, $filter);
            }
        }
        $this->_addInvoiced($ordersCollection,1);
        return $ordersCollection;
    }

    protected function _getSelectedFields($filters)
    {
        return parent::_getSelectedFields($filters);
    }

    public function getReport($filter)
    {
        return parent::getReport($filter);
    }

    public function getReportFields()
    {
        return parent::getReportFields();
    }
}