<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign` ADD `status` BOOLEAN;
");

$table = $installer->getConnection()
	->newTable($installer->getTable('campaign_subcribe_customer'))
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
	->addColumn('no_of_queue', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable' => false
	))
	->addColumn('phone', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
		'nullable' => false
	))
	->addColumn('queue_status', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array())
	->setComment('Queue Table');
$installer->getConnection()->createTable($table);
$installer->endSetup();
