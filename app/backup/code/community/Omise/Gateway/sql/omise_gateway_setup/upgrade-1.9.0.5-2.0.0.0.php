<?php

$installer=$this;

$installer->startSetup();

$installer->run("
	
	CREATE TABLE omise_token (
	`id` INT NOT NULL AUTO_INCREMENT ,
	`customer_id` int( 100 ) NOT NULL ,
	`token` VARCHAR( 100 ) NOT NULL ,
	`created_at` datetime default null
	PRIMARY KEY ( `entity_id` ) 
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
");
																															
$installer->endSetup();

?>