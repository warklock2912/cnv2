<?php
$installer = $this;
$installer->startSetup();

$installer->run(
    "
ALTER TABLE `{$this->getTable('cartreservation_log')}`
ADD `quote_id` int(11) NOT NULL DEFAULT '0' AFTER `action`;
"
);

$installer->endSetup();