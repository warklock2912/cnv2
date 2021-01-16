<?php

$installer = $this;
$installer->startSetup();


$table = $installer->getConnection()
	->newTable($installer->getTable('campaign_subcribe_customer_raffle'))
	->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity' => true,
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
	), ' ID')
	->addColumn('campaign_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable' => false,
	), 'Campaign ID')
	->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable' => false
	))
	->addColumn('customer_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
		'nullable' => false
	))
	->addColumn('email', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
		'nullable' => false
	))
	->addColumn('card_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
		'nullable' => false
	))
	->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
		'nullable' => false
	))
	->addColumn('phone', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
		'nullable' => false
	))
	->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array())
	->addColumn('status', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array())
	->addColumn('option', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array())
	->addColumn('is_winner', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array())
	->setComment('Raffle Customer Table');
$installer->getConnection()->createTable($table);
$installer->endSetup();
