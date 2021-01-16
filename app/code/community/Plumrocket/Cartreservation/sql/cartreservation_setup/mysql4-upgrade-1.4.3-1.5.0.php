<?php
$installer = $this;
$installer->startSetup();

$installer->run(
    "
UPDATE `{$this->getTable('core_config_data')}` SET `value` = CONCAT('00,', `value`) WHERE `path` = 'cartreservation/cart/time' OR `path` = 'cartreservation/checkout/time';

ALTER TABLE `{$this->getTable('cartreservation_item')}`
ADD `emailed` ENUM( '1', '0' ) NOT NULL DEFAULT '0' AFTER `session_id`, 
ADD `alerted` ENUM( '1', '0' ) NOT NULL DEFAULT '0' AFTER `emailed`,
ADD `store_id` INT NOT NULL DEFAULT '0' AFTER `alerted`;
"
);


$installer->endSetup();