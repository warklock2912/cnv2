<?php
$installer = $this;
$installer->startSetup();

$installer->run(
    "
ALTER TABLE  `{$this->getTable('cartreservation_item')}` ADD  `session_id` varchar(32) NOT NULL DEFAULT '' AFTER  `child_id`;
ALTER TABLE `{$this->getTable('cartreservation_item')}` CHANGE `child_id` `child_quote_item_id` INT( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `{$this->getTable('cartreservation_item')}` ADD `child_product_id` INT NOT NULL DEFAULT '0' AFTER `child_quote_item_id`;
"
);


$installer->endSetup();