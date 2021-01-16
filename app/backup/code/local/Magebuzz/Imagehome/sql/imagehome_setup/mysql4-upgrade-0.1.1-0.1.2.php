<?php

$installer = $this;
$installer->startSetup();
$sqlAddColumn = "
    drop procedure if exists AddColumnUnlessExists;
    create procedure AddColumnUnlessExists(
     IN dbName tinytext,
     IN tableName tinytext,
     IN fieldName tinytext,
     IN fieldDef text)
    begin
     IF NOT EXISTS (
      SELECT * FROM information_schema.COLUMNS
      WHERE column_name=fieldName
      and table_name=tableName
      and table_schema=dbName
      )
     THEN
      set @ddl=CONCAT('ALTER TABLE ',tableName,
       ' ADD COLUMN ',fieldName,' ',fieldDef);
      prepare stmt from @ddl;
      execute stmt;
     END IF;
    end
";
$write = Mage::getSingleton('core/resource')->getConnection('core_write');
$write->exec($sqlAddColumn);
$installer->run("call   AddColumnUnlessExists(DATABASE(), '{$this->getTable('imagehome')}', 'html', 'text');
");
$installer->endSetup();
