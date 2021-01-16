<?php
$this->startSetup();
$table = new Varien_Db_Ddl_Table();
$table->setName($this->getTable('omise_gateway/token'));


$table->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array( 'identity'  => true,
                                                                        'unsigned'  => true,
                                                                        'nullable'  => false,
                                                                        'primary'   => true));
$table->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array('nullable' => true));
$table->addColumn('token', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array('nullable' => true));
$table->addColumn('holdername', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array('nullable' => true));
$table->addColumn('ccnumber', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array('nullable' => true));
$table->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array('nullable' => true,'default'=>'NOW()'));

$table->setOption('type', 'InnoDB');
$table->setOption('charset', 'utf8');

$this->getConnection()->createTable($table);

$this->endSetup();

?>