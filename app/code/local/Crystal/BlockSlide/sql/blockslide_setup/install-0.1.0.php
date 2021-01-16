<?php
$installer = $this;
$installer->startSetup();
$table = $installer->getConnection()
	->newTable($installer->getTable('crystal_block_slide'))
	->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity' => true,
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
	), ' ID')
	->addColumn('image', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
		'nullable' => false,
	), ' Image')
	->addColumn('position', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable' => false,
	), 'Position')
	->addColumn('title', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
		'nullable' => true,
	))
	->setComment('Block Slide Table');
$installer->getConnection()->createTable($table);
$installer->endSetup();