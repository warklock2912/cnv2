<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Helper_Data extends Mage_Core_Helper_Abstract
{

    const SALES_REPORT = 'Sales';
    const BESTSELLERS = 'Bestsellers';
    const PROFIT_REPORT = 'Profit';
    const COUNTRY_REPORT = 'Country';
    const NEW_RETURN = 'Newreturn';
    const BY_HOUR = 'Salesbyhour';
    const BY_WEEK = 'Salesbyweek';
    const BY_PRODUCT = 'Salesbyproduct';
    const COUPON_CODE = 'Couponcode';
    //const USER_ACTIVITY = 'Useractivity';
    //const CUSTOMER_GROUP = 'Customergroup';
    //const POST_CODE = 'Postcode';

    public function getReportsTypes()
    {
        return array(
            self::SALES_REPORT => $this->__('Sales Report'),
            self::BESTSELLERS  => $this->__('Bestsellers'),
            self::PROFIT_REPORT  => $this->__('Profit Report'),
            self::COUNTRY_REPORT  => $this->__('Country Report'),
            self::NEW_RETURN  => $this->__('New vs Returning Customers Report'),
            self::BY_HOUR  => $this->__('Sales by Hour Report'),
            self::BY_WEEK  => $this->__('Sales by Weekday Report'),
            self::BY_PRODUCT  => $this->__('Sales by Product Report'),
            self::COUPON_CODE  => $this->__('Sales by Coupon Code Report'),
            //self::USER_ACTIVITY  => $this->__('User activity report'),
            //self::CUSTOMER_GROUP  => $this->__('Customer group report'),
            //self::POST_CODE  => $this->__('Sales by Postcode report'),
        );
    }

    public function getReportName($report)
    {
        $types = $this->getReportsTypes();
        return $types[$report];
    }
}