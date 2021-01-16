<?php
$installer = $this;
$installer->startSetup();

$installer->run(
    "
ALTER TABLE `{$this->getTable('cartreservation_item')}` 
	CHANGE `child_product_id` `child_product_id` VARCHAR( 128 ) NOT NULL,
	CHANGE `child_quote_item_id` `child_quote_item_id` VARCHAR( 128 ) NOT NULL;
"
);

$installer->endSetup();