<?php
    $installer = $this;
    $installer->startSetup();
    $table = $installer->getConnection()
        ->newTable($installer->getTable('crystal_cards'))
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), ' ID')
        ->addColumn('card_token', Varien_Db_Ddl_Table::TYPE_VARCHAR, 64, array(
            'nullable' => false,
        ), 'Card Number')
        ->addColumn('card_number', Varien_Db_Ddl_Table::TYPE_VARCHAR, 64, array(
            'nullable' => false,
        ), 'Card Token')
        ->addColumn('type', Varien_Db_Ddl_Table::TYPE_VARCHAR, 12, array(
            'nullable' => false,
        ), 'Card Type')
        ->addColumn('transaction_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 12, array(
            'nullable' => false,
        ), 'Transaction ID')
        ->addColumn('expired_month', Varien_Db_Ddl_Table::TYPE_VARCHAR, 12, array(
            'nullable' => false,
        ))
        ->addColumn('expired_year', Varien_Db_Ddl_Table::TYPE_VARCHAR, 12, array(
            'nullable' => false,
        ))
        ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => true,
        ), 'Customer ID')
        ->addColumn('is_default', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
            'nullable' => false,
        ));
    $installer->getConnection()->createTable($table);
    $installer->endSetup();
