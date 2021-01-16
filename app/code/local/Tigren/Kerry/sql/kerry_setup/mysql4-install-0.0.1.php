<?php

$installer = $this;

$installer->startSetup();

$installer->run("
	ALTER TABLE {$this->getTable('sales_flat_shipment')} ADD `carrier_type` varchar(10) null;	
	ALTER TABLE {$this->getTable('sales_flat_shipment')} ADD `consignment_no` varchar(20) null;	
	ALTER TABLE {$this->getTable('sales_flat_shipment')} ADD `booking_status` int(1) null;	
	ALTER TABLE {$this->getTable('sales_flat_shipment')} ADD `booking_created_at` TIMESTAMP null;	
	ALTER TABLE {$this->getTable('sales_flat_shipment')} ADD `box_sum` int(2) null;	
	ALTER TABLE {$this->getTable('sales_flat_shipment')} ADD `cod_amount` float(11) null;	
");

$installer->endSetup(); 