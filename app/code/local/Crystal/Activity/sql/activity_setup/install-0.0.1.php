<?php
$installer = $this;
$installer->startSetup();
$table = $installer->getConnection()
	->newTable($installer->getTable('crystal_card_id'))
	->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'auto_increment' => true,
		'primary' => true,
		'nullable' => false,
		'unsigned' => true,
	), ' Customer ID')
	->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,
		'nullable' => false,
	), ' Customer ID')
	->addColumn('card_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 64, array(
	), ' Card ID')
	->addIndex(
		$installer->getIdxName(
			'activity/activity',
			array(
				'card_id',
			),
			Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
		),
		array(
			'card_id',
		),
		array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
	)
	->setComment('News Notification Table');
$installer->getConnection()->createTable($table);
$installer->endSetup();