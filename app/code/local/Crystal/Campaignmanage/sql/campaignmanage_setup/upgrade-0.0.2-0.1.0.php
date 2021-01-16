<?php

$installer = $this;
$installer->startSetup();



$table = $installer->getConnection()
    ->newTable($installer->getTable('campaign_products'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
    ), ' ID')
    ->addColumn('campaign_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ), 'Campaign ID')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false
    ))
    ->setComment('Campaign Product Table');
$installer->getConnection()->createTable($table);
$installer->endSetup();
