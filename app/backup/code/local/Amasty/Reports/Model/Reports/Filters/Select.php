<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Model_Reports_Filters_Select extends Amasty_Reports_Model_Reports_Abstract
{
    protected function _addFieldsToSelect($select, array $fields)
    {
        $select->reset(Zend_Db_Select::COLUMNS);
        foreach ($fields as $field) {
            $select->columns($field);
        }
    }

    protected function _addDateFrom($tableName, $select, $readConnection, $date)
    {
        if (empty($date)) {
            return $select;
        }
        $date = $this->_phpDateToMysqlFrom($date);
        $where = $readConnection->quoteInto($tableName.'.created_at > (?)', $date);
        $select->where($where);
    }

    protected function _addDateTo($tableName, $select, $readConnection, $date)
    {
        if (empty($date)) {
            return $select;
        }
        $date = $this->_phpDateToMysqlTo($date);
        $where = $readConnection->quoteInto($tableName.'.created_at < (?)', $date);
        $select->where($where);
    }

    protected function _addStoreSelect($tableName, $select, $readConnection, $storeIds)
    {
        if (empty($storeIds)) {
            return $select;
        }
        $where = $readConnection->quoteInto($tableName.'.store_id IN (?)', $storeIds);
        $select->where($where);
    }

    protected function _addOrderStatus($tableName, $select, $readConnection, $statusIds)
    {
        if (empty($statusIds)) {
            return $select;
        }
        $where = $readConnection->quoteInto($tableName.'.status IN (?)', $statusIds);
        $select->where($where);
    }

    protected function _applyFilters($tableName, $select, $readConnection, $filters)
    {
        foreach ($filters as $name=>$filter){
            if ( in_array( $name,$this->_allowedFilters ) && !empty($filter) ) {
                $funcName = '_add' . ucfirst($name);
                $this->$funcName($tableName, $select, $readConnection, $filter);
            }
        }
        return $select;
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