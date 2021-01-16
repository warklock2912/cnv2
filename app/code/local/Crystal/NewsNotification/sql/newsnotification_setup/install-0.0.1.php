<?php
$installer = $this;
$installer->startSetup();
$table = $installer->getConnection()
	->newTable($installer->getTable('crystal_newsnotification'))
	->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,
		'nullable' => false,
	), ' Catagory ID')
	->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,
		'nullable' => false,
	), ' Customer ID')
	->setComment('News Notification Table');
$installer->getConnection()->createTable($table);
$installer->endSetup();