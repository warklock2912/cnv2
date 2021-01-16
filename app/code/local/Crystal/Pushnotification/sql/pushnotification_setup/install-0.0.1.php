<?php
$installer = $this;
$installer->startSetup();
$table_noti = $installer->getConnection()
	->newTable($installer->getTable('crystal_notification'))
	->addColumn('notification_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'auto_increment' => true,
		'identity' => true,
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
	), ' Notification ID')
	->addColumn('notification_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
		'nullable' => true,
	), 'Notification Date')
	->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
		'nullable' => false,
	), 'Created At')
	->addColumn('message', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
		'nullable' => false,
	), 'Notification message')
	->addColumn('image', Varien_Db_Ddl_Table::TYPE_VARCHAR, 256, array(
		'nullable' => true,
	), 'Notification image')
	->addColumn('read', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
		'nullable' => false,
	), 'Read?')
	->setComment('Push Notification Table');
$installer->getConnection()->createTable($table_noti);

$table_device = $installer->getConnection()
	->newTable($installer->getTable('crystal_notification_device'))
	->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'auto_increment' => true,
		'identity' => true,
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
	), 'ID')
	->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
		'nullable' => false,
	), 'User Id')
	->addColumn('device_token', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
		'nullable' => false,
	), 'Device Token')
	->addColumn('platform', Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
		'nullable' => false,
	), 'Device Platform ')
	->setComment('Device Notification Table');
$installer->getConnection()->createTable($table_device);

$installer->endSetup();