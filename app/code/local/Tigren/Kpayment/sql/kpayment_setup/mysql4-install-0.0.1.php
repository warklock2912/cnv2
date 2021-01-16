<?php

$installer = $this;

$installer->startSetup();
$date = Mage::getSingleton('core/date')->gmtdate();

$table = $installer->getConnection()
    ->newTable($installer->getTable('kpayment_credit_reference'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Entity ID')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default' => 0
    ), 'Order ID')
    ->addColumn('order_increment', Varien_Db_Ddl_Table::TYPE_TEXT, '80', array(
        'nullable'  => false,
        'default' => ''
    ), 'Order Increment')
    ->addColumn('method', Varien_Db_Ddl_Table::TYPE_TEXT, '100', array(
        'nullable' => false,
        'default' => ''
    ), 'Method')
    ->addColumn('response_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        'default' => $date
    ), 'Response at')
    ->addColumn('charge_id', Varien_Db_Ddl_Table::TYPE_TEXT, '100', array(
        'nullable'  => false,
        'default' => ''
    ), 'Charge ID')
    ->addColumn('object', Varien_Db_Ddl_Table::TYPE_TEXT, '30', array(
        'nullable'  => false,
        'default' => ''
    ), 'Object response')
    ->addColumn('amount', Varien_Db_Ddl_Table::TYPE_TEXT, '30', array(
        'nullable'  => false,
        'default' => ''
    ), 'Charge Amount')
    ->addColumn('currency', Varien_Db_Ddl_Table::TYPE_TEXT, '30', array(
        'nullable'  => false,
        'default' => ''
    ), 'Currency')
    ->addColumn('transaction_state', Varien_Db_Ddl_Table::TYPE_TEXT, '100', array(
        'nullable'  => false,
        'default' => ''
    ), 'Transaction State')
    ->addColumn('source_id', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, '100', array(
        'nullable'  => false,
        'default' => ''
    ), 'Source Id')
    ->addColumn('source_object', Varien_Db_Ddl_Table::TYPE_TEXT, '30', array(
        'nullable'  => false,
        'default' => ''
    ), 'Source Object')
    ->addColumn('source_brand', Varien_Db_Ddl_Table::TYPE_TEXT, '30', array(
        'nullable'  => false,
        'default' => ''
    ), 'Source Brand')
    ->addColumn('source_card_masking', Varien_Db_Ddl_Table::TYPE_TEXT, '30', array(
        'nullable'  => false,
        'default' => ''
    ), 'Source Card Masking')
    ->addColumn('created', Varien_Db_Ddl_Table::TYPE_TEXT, '50', array(
        'nullable'  => false,
        'default' => ''
    ), 'Created')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, '20', array(
        'nullable'  => false,
        'default' => ''
    ), 'Status')
    ->addColumn('livemode', Varien_Db_Ddl_Table::TYPE_TEXT, '20', array(
        'nullable'  => false,
        'default' => ''
    ), 'Livemode')
    ->addColumn('failure_code', Varien_Db_Ddl_Table::TYPE_TEXT, '30', array(
        'nullable'  => false,
        'default' => ''
    ), 'Failure Code')
    ->addColumn('failure_message', Varien_Db_Ddl_Table::TYPE_TEXT, '50', array(
        'nullable'  => false,
        'default' => ''
    ), 'Failure Message')
    ->addColumn('authen_url', Varien_Db_Ddl_Table::TYPE_TEXT, '100', array(
        'nullable'  => false,
        'default' => ''
    ), 'URL Authen for 3ds')
    ->addColumn('settlement_info', Varien_Db_Ddl_Table::TYPE_TEXT, '200', array(
        'nullable'  => false,
        'default' => ''
    ), 'Settlement')
    ->addColumn('refund_info', Varien_Db_Ddl_Table::TYPE_TEXT, '200', array(
        'nullable'  => false,
        'default' => ''
    ), 'Refund')
    ->addColumn('token_id', Varien_Db_Ddl_Table::TYPE_TEXT, '100', array(
        'nullable'  => false,
        'default' => ''
    ), 'ID Token')
    ->addColumn('token_card_id', Varien_Db_Ddl_Table::TYPE_TEXT, '100', array(
        'nullable'  => false,
        'default' => ''
    ), 'CARD TOKEN')
    ->addColumn('inquiry_status', Varien_Db_Ddl_Table::TYPE_TEXT, '255', array(
        'nullable'  => true,
        'default' => ''
    ), 'Inquiry Status')
    ->addColumn('inquiry_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        'default' => ''
    ), 'Inquiry Date');
$installer->getConnection()->createTable($table);

$installer->getConnection()->addIndex(
    $installer->getTable('kpayment_credit_reference'),
    $installer->getIdxName(
        'kpayment_credit_reference',
        array('order_increment'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('order_increment'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->getConnection()->addIndex(
    $installer->getTable('kpayment_credit_reference'),
    $installer->getIdxName(
        'kpayment_credit_reference',
        array('charge_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('charge_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->endSetup();