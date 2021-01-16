<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Model_Reports_Country extends Amasty_Reports_Model_Reports_Filters_Select
{
    protected $_allowedFilters = array(
        'StoreSelect',
        'OrderStatus',
        'DateFrom',
        'DateTo',
    );

    protected function _getSelectedFields($filters)
    {
        return array('main_table.country_id as country',
                     'COUNT(*) as count',
                     'SUM(flat_order.total_item_count) as total_item_count',
                     'SUM(flat_order.base_grand_total) as base_grand_total',
                     'SUM(flat_order.base_tax_amount) as base_tax_amount',
                     'SUM(flat_order.base_shipping_amount) as base_shipping_amount',
                     'SUM(flat_order.base_discount_amount) as base_discount_amount',
                     'SUM(flat_order.base_total_invoiced) as base_total_invoiced',
        );
    }

    public function getReport($filters)
    {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_write');
        $select = $readConnection->select();
        $select
            ->from(
                array(
                    'main_table' => Mage::getSingleton('core/resource')->getTableName('sales/quote_address'))
                )
            ->joinInner(
                array(
                    'flat_order' => Mage::getSingleton('core/resource')->getTableName('sales/order')),
                'main_table.quote_id = flat_order.quote_id'
            );
        $select->where('main_table.country_id IS NOT NULL');
        $select->where('main_table.address_type=\'shipping\'');
        $select->group('main_table.country_id');
        $this->_addFieldsToSelect($select,$this->_getSelectedFields($filters));
        $filters = $this->_prepareFields($filters);
        $this->_applyFilters('flat_order',$select,$readConnection,$filters);
        $results = $readConnection->fetchAll( $select );
        array_walk($results, array($this,'loadCountryNames'));
        return $results;
    }

    function loadCountryNames(&$item, $key)
    {
        $country = Mage::getModel('directory/country')->loadByCode($item['country']);
        if ($country->getName())
            $item['country'] = $country->getName();
    }

    public function getReportFields()
    {
        return array('ReportName','DateFrom', 'DateTo', 'OrderStatus','StoreSelect');
    }
}