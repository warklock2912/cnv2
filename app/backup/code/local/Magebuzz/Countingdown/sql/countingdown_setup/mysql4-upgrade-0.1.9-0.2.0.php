<?php
$installer = $this;
$installer->startSetup();
$installer->run("
	ALTER TABLE {$this->getTable('cataloginventory_stock_item')} ADD `is_store_only` int(11) default 0;
");
$installer->endSetup(); 