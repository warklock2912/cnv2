<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


/** @var Amasty_Customform_Model_Mysql4_Setup $installer */
$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('amcustomform/form'))
    ->addColumn(
        'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'Form Id'
    )
    ->addColumn(
        'is_deleted', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable' => false,
        'default'  => '0'
    ), 'Is Deleted'
    )
    ->addColumn(
        'code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => false
    ), 'Code'
    )
    ->addColumn(
        'success_url', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => true
    ), 'Success Url'
    )
    ->addColumn(
        'action', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => true,
        'default'  => null
    ), 'Action'
    )
    ->addColumn(
        'captcha', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable' => false,
        'default'  => '0'
    ), 'Captcha'
    )
    ->addColumn(
        'notification', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable' => false,
        'default'  => '0'
    ), 'Notification'
    )
    ->setComment('Amasty CustomForm From');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('amcustomform/form_store'))
    ->addColumn(
        'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary'  => true
    ), 'Id'
    )
    ->addColumn(
        'form_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ), 'Form Id'
    )
    ->addColumn(
        'store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ), 'Store Id'
    )
    ->addColumn(
        'title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => true,
    ), 'Title'
    )
    ->setComment('Amasty CustomForm Link store_form');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('amcustomform/form_line'))
    ->addColumn(
        'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'Line Id'
    )
    ->addColumn(
        'form_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => true,
    ), 'Form ID'
    )
    ->addColumn(
        'is_deleted', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable' => false,
        'default'  => '0'
    ), 'Is Deleted'
    )
    ->addColumn(
        'title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => false
    ), 'Title'
    )
    ->addColumn(
        'action', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => true,
        'default'  => null
    ), 'Action'
    )
    ->addColumn(
        'sort_order', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default'  => '0',
    ), 'Line Sort Order'
    )
    ->setComment('Amasty CustomForm From');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('amcustomform/form_field'))
    ->addColumn(
        'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'Form Id'
    )
    ->addColumn(
        'field_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false
    ), 'Field Id'
    )
    ->addColumn(
        'line_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false
    ), 'Line Id'
    )
    ->addColumn(
        'is_deleted', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable' => false,
        'default'  => '0'
    ), 'Is Deleted'
    )
    ->addColumn(
        'sort_order', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default'  => '0',
    ), 'Form Sort Order'
    )
    ->addColumn(
        'rewrite_default_value', Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
        array(
            'nullable' => false,
            'default'  => '0'
        ), 'Rewrite Default Value'
    )
    ->addColumn(
        'default_value', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'nullable' => true,
        'default'  => null
    ), 'Default Value'
    )
    ->setComment('Amasty CustomForm Field');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('amcustomform/form_submit'))
    ->addColumn(
        'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'Form Submit Id'
    )
    ->addColumn(
        'form_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false
    ), 'Form Id'
    )
    ->addColumn(
        'store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'default'  => '0'
    ), 'Store Id'
    )
    ->addColumn(
        'submitted', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable' => false,
        'default'  => '0000-00-00 00:00:00',
    ), 'Submitted Date'
    )
    ->addColumn(
        'customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false
    ), 'Customer Id'
    )
    ->addColumn(
        'values', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'nullable' => false
    ), 'Values'
    )
    ->addColumn(
        'ip', Varien_Db_Ddl_Table::TYPE_VARCHAR, 15, array(
        'nullable' => true,
        'default' => null
    ), 'Customer IP address'
    )
    ->setComment('Amasty CustomForm From');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('amcustomform/field'))
    ->addColumn(
        'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'Field Id'
    )
    ->addColumn(
        'is_deleted', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable' => false,
        'default'  => '0'
    ), 'Is Deleted'
    )
    ->addColumn(
        'code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => false
    ), 'Code'
    )
    ->addColumn(
        'input_type', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => false
    ), 'Input Type'
    )
    ->addColumn(
        'frontend_class', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'nullable' => true,
        'default'  => null
    ), 'Frontend Class'
    )
    ->addColumn(
        'default_value', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'nullable' => true,
        'default'  => null
    ), 'Default Value'
    )
    ->addColumn(
        'required', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable' => false,
        'default'  => 0
    ), 'Required'
    )
    ->addColumn(
        'max_length', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => true,
        'default'  => null
    ), 'Max Length'
    )
    ->setComment('Amasty CustomForm Field');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('amcustomform/field_store'))
    ->addColumn(
        'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary'  => true
    ), 'Field Id'
    )
    ->addColumn(
        'field_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ), 'Field Id'
    )
    ->addColumn(
        'store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ), 'Store Id'
    )
    ->addColumn(
        'label', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => true,
    ), 'Label'
    )
    ->setComment('Amasty CustomForm Link store_field');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('amcustomform/field_option'))
    ->addColumn(
        'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary'  => true
    ), 'Option Id'
    )
    ->addColumn(
        'field_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ), 'Field Id'
    )
    ->addColumn(
        'position', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'default'  => '0'
    ), 'Position'
    )
    ->addColumn(
        'is_default', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default'  => '0',
    ), 'Position'
    )
    ->setComment('Amasty CustomForm Field Option');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('amcustomform/field_option_store'))
    ->addColumn(
        'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary'  => true
    ), 'Id'
    )
    ->addColumn(
        'field_option_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ), 'Field Id'
    )
    ->addColumn(
        'store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ), 'Store Id'
    )
    ->addColumn(
        'label', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable' => true,
    ), 'Label'
    )
    ->setComment('Amasty CustomForm Field Option');
$installer->getConnection()->createTable($table);

$installer->endSetup();