<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign_subcribe_customer_raffle` MODIFY `option` VARCHAR(64) ;
");
$installer->endSetup();
