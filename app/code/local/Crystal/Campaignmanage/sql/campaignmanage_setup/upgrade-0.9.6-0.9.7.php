<?php
$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
	->newTable($installer->getTable('campaign_raffle_online_products'))
	->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity' => true,
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
	), ' ID')
	->addColumn('raffle_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable' => false,
	), 'Raffle ID')
	->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable' => false
	))
	->addColumn('options', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable' => false
	))
	->addForeignKey('FK_RAFFLE_PRODUCT', 'raffle_id', 'campaign_raffle_online', 'id', 'ACTION_CASCADE', null)
	->setComment('Raffle Online Product Table');
$installer->getConnection()->createTable($table);
$installer->endSetup();
