<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Model_Reports_Useractivity extends Amasty_Reports_Model_Reports_Filters_Select
{

    protected $_allowedFilters = array(
        'Period',
        'DateFrom',
        'DateTo',
        'StoreSelect'
    );

    protected function _getSelectedFields($filters)
    {
        return array('main_table.country_id as country',
                     'SUM(flat_order.total_item_count) as soldByCountry'
        );
    }

    public function getReport($filters)
    {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_write');
        $select = $readConnection->select();


        $select->from(
                array(
                    'main_table' => Mage::getSingleton('core/resource')->getTableName('sales/order'))
            );

        //$select->where('main_table.base_total_invoiced > 0');
        $select->group('main_table.country_id');

        $this->_addFieldsToSelect($select,array(
            'date(main_table.created_at) as period',
            'SUM(main_table.order_id)'
        ));



        //Mage::getSingleton('core/resource')->getTableName('sales/order')
        /*
                var_dump((string)$select);
                exit;
        */
        $results = $readConnection->fetchAll( $select );

        array_walk($results, array($this,'loadCountryNames'));

        return $results;
    }

    function loadCountryNames(&$item, $key)
    {
        $country = Mage::getModel('directory/country')->loadByCode($item['country']);
        $item['country'] = $country->getName();
    }


    public function getReportFields()
    {
        return array('ReportName','DateFrom', 'DateTo', 'Period', 'StoreSelect');
    }
}