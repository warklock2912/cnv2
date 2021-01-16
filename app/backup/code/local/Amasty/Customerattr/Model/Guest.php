<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */
class Amasty_Customerattr_Model_Guest extends Mage_Core_Model_Abstract
{
    /**
     * types for add fields in mysql
     */
    private $eavTypes
        = array(
            "varchar"             => "varchar(255)",
            "tier_price"          => "decimal(12,4)",
            "text"                => "text",
            "media_gallery_value" => "varchar(255)",
            "media_gallery"       => "varchar(255)",
            "int"                 => "int(11)",
            "group_price"         => "decimal(12,4)",
            "gallery"             => "varchar(255)",
            "decimal"             => "decimal(12,4)",
            "datetime"            => "datetime",
            "static"              => "varchar(255)"
        );

    public function _construct()
    {
        parent::_construct();
        $this->_init('amcustomerattr/guest');
    }

    /**
     * delete $fields from amcustomerattr/guest
     */
    public function deleteFields($fields)
    {
        if (empty($fields)) {
            return;
        }
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');
        $tableName = $resource->getTableName("amcustomerattr/guest");
        $columnsStr = implode(
            ',', array_map(
            function ($field) {
                return "DROP COLUMN `$field`";
            }, $fields
        )
        );
        $sql = "ALTER TABLE `" . $tableName . "` " . $columnsStr;
        $connection->query($sql);
        $connection->resetDdlCache();
    }

    /**
     * add fields to amcustomerattr/guest
     * with the appropriate $types
     */
    public function addFields($fields, $types)
    {
        if (empty($fields)) {
            return;
        }
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');
        $tableName = $resource->getTableName("amcustomerattr/guest");
        $eavTypes = $this->eavTypes;
        $columnsStr = implode(
            ',', array_map(
            function ($field) use ($types, $eavTypes) {
                return "ADD COLUMN `$field` {$eavTypes[$types[$field]]} ";
            }, $fields
        )
        );
        $sql = "ALTER TABLE `" . $tableName . "` " . $columnsStr;
        $connection->query($sql);
        $connection->resetDdlCache();
    }

    /**
     * Before setData
     * -Implode multiselect fields;
     * -Formatting Date fields;
     */
    public function setData($fields, $value = null)
    {
        $fieldTypes = Mage::helper('amcustomerattr/guest')->getFieldTypes();
        if (is_array($fields)) {
            foreach ($fields as $key => &$field) {
                if (is_array($field)) {
                    $field = implode(",", $field);
                } else if (array_key_exists($key, $fieldTypes)
                    && strcmp(
                        $fieldTypes[$key], 'datetime'
                    ) == 0
                ) {
                    $field = Mage::helper('amcustomerattr/guest')->formatDate(
                        $field
                    );
                }

            }
        } else if (array_key_exists($fields, $fieldTypes)
            && strcmp(
                $fieldTypes[$fields], 'datetime'
            ) == 0
        ) {
            $fields = Mage::helper('amcustomerattr/guest')->formatDate($fields);
        }
        parent::setData($fields, $value);
    }

    /**
     * function is used in the emails templates
     * guest.custom('attribute_code')
     */
    public function custom($attributeCode)
    {
        return Mage::helper('amcustomerattr')->processingValue(
            $this, $attributeCode
        );
    }
}