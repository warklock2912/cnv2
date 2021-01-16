<?php
$installer = $this;
$installer->startSetup();
$table = $installer->getConnection()
	->newTable($installer->getTable('crystal_confirm_order'))
	->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity' => true,
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
	), ' ID')
	->addColumn('order_increment_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 64, array(
		'unsigned' => true,
		'nullable' => false,
	), ' Order Increment ID')
	->addColumn('is_confirmed', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
		'unsigned' => true,
		'nullable' => false,
	), 'Is Confirmed')
	->setComment('Check order Confirmed Table');
$installer->getConnection()->createTable($table);
$installer->endSetup();