<?php
$installer = $this;
$installer->startSetup();
$table = $installer->getConnection()
	->newTable($installer->getTable('campaign'))
	->addColumn('campaign_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity' => true,
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
	), ' Campaign ID')
	->addColumn('campaign_type', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable' => false,
	), 'Campaign Type')
	->addColumn('campaign_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 128, array(
		'nullable' => false,
	), 'Campaign Name')
	->addColumn('queue_prefix', Varien_Db_Ddl_Table::TYPE_VARCHAR, 128, array(
		'nullable' => true,
	))
	->addColumn('start_register_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
		'nullable' => true,
	), 'Starting register time')
	->addColumn('end_register_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
		'nullable' => true,
	), 'Ending register time')
	->addColumn('no_of_part', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable' => true,
	), 'No. of participants')
	->addColumn('app_display', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
		'nullable' => false,
	), 'Display on app')
	->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable' => true,
	), 'Category Id')
	->addColumn('dealerlocator_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable' => false,
	), 'Dealerlocator id')
	->addForeignKey('Dealerlocator foreign key', 'dealerlocator_id', 'dealerlocator', 'dealerlocator_id', null, null)
	->setComment('Campaign Table');
$installer->getConnection()->createTable($table);
$installer->endSetup();