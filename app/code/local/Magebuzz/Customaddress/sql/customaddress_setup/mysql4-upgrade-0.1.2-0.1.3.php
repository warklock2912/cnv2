<?php
$installer = $this;
$installer->startSetup();

$installer->run("
	ALTER TABLE {$this->getTable('sales_flat_quote_address')} ADD COLUMN `city_id` VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL AFTER `fax`;
	ALTER TABLE {$this->getTable('sales_flat_order_address')} ADD COLUMN `city_id` VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL AFTER `fax`;
	ALTER TABLE {$this->getTable('sales_flat_quote_address')} ADD COLUMN `subdistrict_id` VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL AFTER `fax`;
	ALTER TABLE {$this->getTable('sales_flat_order_address')} ADD COLUMN `subdistrict_id` VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL AFTER `fax`;
	ALTER TABLE {$this->getTable('sales_flat_quote_address')} ADD COLUMN `subdistrict` VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL AFTER `fax`;
	ALTER TABLE {$this->getTable('sales_flat_order_address')} ADD COLUMN `subdistrict` VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL AFTER `fax`; 
");

$installer->endSetup();