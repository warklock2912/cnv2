<?php

$installer = $this;

$installer->startSetup();
$date = Mage::getSingleton('core/date')->gmtdate();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->removeAttribute('customer','notified_member');

$table = $installer->getConnection()
    ->newTable($installer->getTable('member_notify_vip'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Entity ID')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_TEXT, '80', array(
        'nullable'  => false,
        'default' => ''
    ), 'Customer ID')
    ->addColumn('notified_member', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default' => 0
    ), 'Notified Member');
$installer->getConnection()->createTable($table);


$installer->endSetup();