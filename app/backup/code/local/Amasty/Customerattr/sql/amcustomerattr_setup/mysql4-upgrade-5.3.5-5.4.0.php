<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */

$installer = $this;

$installer->startSetup();

$installer->run(
    "
CREATE TABLE IF NOT EXISTS `{$this->getTable('amcustomerattr/guest')}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
"
);


$installer->endSetup();