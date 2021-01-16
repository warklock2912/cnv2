<?php 

$installer=$this;

$installer->startSetup();

$installer->run("
	DROP TABLE IF EXISTS tb_ordersmsnotification;
	CREATE TABLE tb_ordersmsnotification (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `orderid` int(11) NOT NULL,
	  `eventname` varchar(35) NOT NULL DEFAULT '',
	  `tracking_number` varchar(35) NOT NULL DEFAULT '',
	  `phonenumber` varchar(50) DEFAULT '',
	  `message` varchar(255) DEFAULT '',
	  `createdate` datetime DEFAULT NULL,
	  `createby` varchar(100) DEFAULT '',
	  `processstatus` varchar(2) DEFAULT '',
	  `processstatusmsg` varchar(50) DEFAULT '',
	  `processstartdate` datetime DEFAULT NULL,
	  `processenddate` datetime DEFAULT NULL,
	  `priority` int(2) DEFAULT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
");
																															
$installer->endSetup();

?>