<?php
$installer = $this;
$installer->startSetup();
$table = $installer->getConnection()
    ->newTable($installer->getTable('campaign_crop_and_drop'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
    ), ' ID')
    ->addColumn('title', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => false,
    ), 'Title')
    ->addColumn('content', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => true,
    ), 'Content')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 64, array(
        'nullable' => false,
    ), 'Product Id')
    ->addColumn('size', Varien_Db_Ddl_Table::TYPE_VARCHAR, 64, array(
        'nullable' => false,
    ), 'Size')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable' => false,
    ), 'Created At')
    ->setComment('Campaign Table');
$installer->getConnection()->createTable($table);
$installer->endSetup();