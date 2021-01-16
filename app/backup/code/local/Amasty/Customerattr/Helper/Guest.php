<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */
class Amasty_Customerattr_Helper_Guest extends Mage_Core_Helper_Abstract
{
    /**
     * get list of fields for amcustomerattr/guest
     */
    public function getFields()
    {
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_read');
        $tableName = $resource->getTableName('amcustomerattr/guest');
        $sql = "SHOW COLUMNS FROM `" . $tableName
            . "` WHERE Field NOT IN ('id','order_id')";
        $tableInfo = $connection->fetchAssoc($sql);
        $columns = array();
        if (is_array($tableInfo) && !empty($tableInfo)) {
            foreach ($tableInfo as $column) {
                $columns[] = $column['Field'];
            }
        }
        return $columns;
    }

    /**
     * update fields in table amcustomerattr/guest
     * in accordance with current Customer Attributes
     */
    public function update()
    {
        $model = Mage::getModel('customer/attribute');
        $collection = $model->getCollection();
        $filters = array(
            "is_user_defined = 1",
            "attribute_code != 'customer_activated' "
        );
        $collection = Mage::helper('amcustomerattr')->addFilters(
            $collection, 'eav_attribute', $filters
        );
        $attributeName = array();
        $attributeType = array();
        foreach ($collection as $attribute) {
            $attributeName[] = $attribute['attribute_code'];
            $attributeType[$attribute['attribute_code']]
                = $attribute['backend_type'];
        }

        $currentFields = Mage::helper('amcustomerattr/Guest')->getFields();

        $namesAdd = array_diff($attributeName, $currentFields);

        $namesDel = array_diff($currentFields, $attributeName);

        $model = Mage::getModel("amcustomerattr/guest");

        $model->deleteFields($namesDel);
        $model->addFields($namesAdd, $attributeType);
    }

    /**
     * used in setData Guest Model for fields with types like date
     */
    public function formatDate($date)
    {
        if (empty($date)) {
            return null;
        }
        // unix timestamp given - simply instantiate date object
        if (preg_match('/^[0-9]+$/', $date)) {
            $date = new Zend_Date((int)$date);
        } // international format
        else if (preg_match(
            '#^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$#', $date
        )) {
            $zendDate = new Zend_Date();
            $date = $zendDate->setIso($date);
        } // parse this date in current locale, do not apply GMT offset
        else {
            $date = Mage::app()->getLocale()->date(
                $date,
                Mage::app()->getLocale()->getDateFormat(
                    Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
                ),
                null, false
            );
        }
        return $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
    }

    /**
     * get types for customattr fields of table
     */
    public function getFieldTypes()
    {
        $model = Mage::getModel('customer/attribute');
        $collection = $model->getCollection();
        $filters = array(
            "is_user_defined = 1",
            "attribute_code != 'customer_activated' "
        );
        $collection = Mage::helper('amcustomerattr')->addFilters(
            $collection, 'eav_attribute', $filters
        );


        $attributeType = array();
        foreach ($collection as $attribute) {
            $attributeType[$attribute['attribute_code']]
                = $attribute['backend_type'];
        }
        return $attributeType;
    }

}