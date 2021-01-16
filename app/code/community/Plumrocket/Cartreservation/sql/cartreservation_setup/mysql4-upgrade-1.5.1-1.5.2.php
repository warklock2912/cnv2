<?php
$installer = $this;
$installer->startSetup();

$installer->run(
    "
CREATE TABLE IF NOT EXISTS `{$this->getTable('cartreservation_log')}` (
	`entity_id` int(11) NOT NULL AUTO_INCREMENT,
	`action` varchar(32) NOT NULL DEFAULT 'add',
	`product_id` int(11) NOT NULL,
	`customer_id` int(11) NOT NULL,
	`product_qty` int(11) NOT NULL,
	`old_qty` int(11) NOT NULL,
	`qty` int(11) NOT NULL,
	`cr_qty` int(11) NOT NULL,
	`cr_corrected` int(11) NOT NULL,
	`referer` varchar(256) NOT NULL,
	`date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`sid` varchar(64) NOT NULL,
	PRIMARY KEY (`entity_id`),
	KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
"
);


$installer->endSetup();