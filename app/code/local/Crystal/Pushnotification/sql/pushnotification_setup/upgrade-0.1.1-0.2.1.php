<?php
$installer = $this;
$installer->startSetup();
$table = $installer->getConnection()
	->newTable($installer->getTable('crystal_newsnotification_list'))
	->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'auto_increment' => true,
		'identity' => true,
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
	), ' ID')
	->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
		'nullable' => false,
	), ' Customer ID')
	->addColumn('title', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Title')
	->addColumn('description', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'Description')
	->addColumn('image', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(), 'image')
	->addColumn('type', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(), 'type')
	->addColumn('content_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(), 'Content Id')
	->setComment('News Notification List Table');
$installer->getConnection()->createTable($table);

$tableCustomerNotificationList = $installer->getConnection()
	->newTable($installer->getTable('crystal_customer_notification_list'))
	->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'auto_increment' => true,
		'identity' => true,
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
	), ' ID')
	->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,
		'nullable' => false,
	), ' Customer ID')
	->addColumn('notification_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,
		'nullable' => false,
	), ' Notification ID')
	->addForeignKey('Notification Id', 'notification_id', 'crystal_newsnotification_list', 'id', 'ACTION_CASCADE', $onUpdate = null)
	->setComment('Customer\'s  Notification List Table');
$installer->getConnection()->createTable($tableCustomerNotificationList);
$installer->endSetup();