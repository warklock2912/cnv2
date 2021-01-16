<?php

$installer = $this;
$installer->startSetup();

try {
      // Required tables
      $statusTable = $installer->getTable('sales/order_status');
      $statusStateTable = $installer->getTable('sales/order_status_state');

      // Insert statuses
      $installer->getConnection()->insertArray(
        $statusTable,
        array('status','label'),
        array(array('status' => 'Pending_2C2P', 'label' => 'Pending 2C2P'))
        );

      // Insert states and mapping of statuses to states
      $installer->getConnection()->insertArray(
        $statusStateTable,
        array(
          'status',
          'state',
          'is_default'
          ),
        array(
          array(
            'status' => 'Pending_2C2P',
            'state' => 'Pending_2C2P',
            'is_default' => 0
            )
          )
        );
} catch (Exception $e) {}

  /**
  * Add column into sales_flat_order table.
  */
  try {
        $installer->run("
              ALTER TABLE {$this->getTable('sales_flat_order')}
              ADD COLUMN transaction_ref VARCHAR(45) AFTER state ,
              ADD COLUMN statuscode      VARCHAR(45) AFTER status
              ");
  } catch (Exception $e) {}

  /**
  * Create p2c2p_token table
  */
  if(!$installer->tableExists('p2c2p/token')) {

        $table = $installer->getConnection()->newTable(
            $installer->getTable('p2c2p_token')
            )->addColumn(
            'p2c2p_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )->addColumn(
            'user_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false]
            )->addColumn(
            'stored_card_unique_id',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            255,
            ['nullable' => false]
            )->addColumn(
            'masked_pan',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            255,
            ['nullable' => false]
            )->addColumn(
            'created_time',
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT]
            )->addIndex(
            $installer->getIdxName('p2c2p_token', ['p2c2p_id']),
            ['p2c2p_id']
            )->addForeignKey(
            $installer->getFkName('p2c2p_token', 'user_id', 'customer_entity', 'entity_id'),
            'user_id',
            $installer->getTable('customer_entity'),
            'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE
            );

            $installer->getConnection()->createTable($table);
      }

   /**
    * Create p2c2p_meta table.
    */
   if(!$installer->tableExists('p2c2p/meta')) {

        $table = $installer->getConnection()->newTable(
            $installer->getTable('p2c2p_meta')
            )->addColumn(
            'p2c2p_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]            
            )->addColumn(
            'order_id',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            20,
            ['unsigned' => true, 'nullable' => false]            
            )->addColumn(
            'user_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            ['nullable' => false]            
            )->addColumn(
            'version',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            5,
            ['nullable' => false]            
            )->addColumn(
            'request_timestamp',
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT]
            )->addColumn(
            'merchant_id',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            25,
            ['nullable' => false]
            )->addColumn(
            'invoice_no',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            50,
            ['nullable' => true]
            )->addColumn(
            'currency',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            3,
            ['nullable' => true]
            )->addColumn(
            'amount',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            20,
            ['nullable' => true]
            )->addColumn(
            'transaction_ref',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            15,
            ['nullable' => true]
            )->addColumn(
            'approval_code',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            6,
            ['nullable' => true]
            )->addColumn(
            'eci',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            2,
            ['nullable' => true]
            )->addColumn(
            'transaction_datetime',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            100,
            ['nullable' => true]
            )->addColumn(
            'payment_channel',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            3,
            ['nullable' => true]
            )->addColumn(
            'payment_status',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            3,
            ['nullable' => true]
            )->addColumn(
            'channel_response_code',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            2,
            ['nullable' => true]
            )->addColumn(
            'channel_response_desc',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            255,
            ['nullable' => true]
            )->addColumn(
            'masked_pan',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            16,
            ['nullable' => true]
            )->addColumn(
            'stored_card_unique_id',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            20,
            ['nullable' => true]
            )->addColumn(
            'backend_invoice',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            12,
            ['nullable' => true]
            )->addColumn(
            'paid_channel',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            30,
            ['nullable' => true]
            )->addColumn(
            'paid_agent',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            30,
            ['nullable' => true]
            )->addColumn(
            'recurring_unique_id',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            20,
            ['nullable' => true]
            )->addColumn(
            'user_defined_1',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            255,
            ['nullable' => true]
            )->addColumn(
            'user_defined_2',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            255,
            ['nullable' => true]
            )->addColumn(
            'user_defined_3',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            255,
            ['nullable' => true]
            )->addColumn(
            'user_defined_4',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            255,
            ['nullable' => true]
            )->addColumn(
            'user_defined_5',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            255,
            ['nullable' => true]
            )->addColumn(
            'browser_info',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            50,
            ['nullable' => true]
            )->addColumn(
            'ippPeriod',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            2,
            ['nullable' => true]
            )->addColumn(
            'ippInterestType',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            1,
            ['nullable' => true]
            )->addColumn(
            'ippInterestRate',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            5,
            ['nullable' => true]
            )->addColumn(
            'ippMerchantAbsorbRate',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            5,
            ['nullable' => true]
            )->addIndex(
            $installer->getIdxName('p2c2p_meta', ['p2c2p_id']),
            ['p2c2p_id']
            );

            $installer->getConnection()->createTable($table);
      }

      $installer->endSetup(); 