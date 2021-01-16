<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */
$this->startSetup();
$this->run("
  CREATE TABLE shippop (
    `shippop_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `shippop_purchase_id` int(11) NOT NULL,
    `order_id` int(11) NOT NULL,
    `tracking_code` varchar(11) NOT NULL DEFAULT '',
    `courier_tracking_code` varchar(20) NOT NULL,
    `courier_code` varchar(10) DEFAULT '',
    `status` varchar(10) DEFAULT 'booking',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
      PRIMARY KEY (`shippop_id`)
  )
");
$this->endSetup();