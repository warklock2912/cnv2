<?php
$installer = $this;
$installer->startSetup();

$installer->run(
    "
CREATE TABLE IF NOT EXISTS `{$this->getTable('cartreservation_item')}` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `quote_id` int(11) NOT NULL,
  `quote_item_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `cart_date` int(11) NOT NULL DEFAULT '0',
  `cart_time` int(11) NOT NULL DEFAULT '0',
  `checkout_date` int(11) NOT NULL DEFAULT '0',
  `checkout_time` int(11) NOT NULL DEFAULT '0',
  `qty` int(11) NOT NULL DEFAULT '0',
  `child_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`item_id`),
  KEY `quote_id` (`quote_id`),
  KEY `product_id` (`product_id`),
  KEY `quote_item_id` (`quote_item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
"
);


$installer->endSetup();
