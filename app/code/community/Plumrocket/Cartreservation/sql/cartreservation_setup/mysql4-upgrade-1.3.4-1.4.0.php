<?php
$installer = $this;
$installer->startSetup();

$installer->run(
    "
ALTER TABLE `{$this->getTable('cartreservation_item')}` CHANGE `session_id` `session_id` VARCHAR(64) NOT NULL DEFAULT ''
"
);


$installer->endSetup();