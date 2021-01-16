<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('p2c2p/token'),'is_default', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable'  => true,
        'default' => 0,
        'comment'   => 'Default Card'
    ));
$installer->endSetup();