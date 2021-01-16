<?php

$installer = $this;
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS crystal_newsnotification_list;
DROP TABLE IF EXISTS crystal_customer_notification_list;
");
$table = $installer->getConnection()
	->newTable($installer->getTable('crystal_notification_list'))
	->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'auto_increment' => true,
		'identity' => true,
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
	), ' ID')
	->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
		'nullable' => false,
	), ' created at')
	->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false), 'Customer ID')
	->addColumn('type', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(), 'type')
	->addColumn('content_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(), 'Content Id')
	->addColumn('notification_status', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(), 'Notification Status')
	->setComment(' Notification List Table');
$installer->getConnection()->createTable($table);
$installer->endSetup();
