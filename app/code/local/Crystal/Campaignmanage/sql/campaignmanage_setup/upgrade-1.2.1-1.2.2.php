<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign_subcribe_customer_raffle` DROP FOREIGN KEY FK_RAFFLE_RELATION_CAMPAIGN ;
ALTER TABLE `campaign_subcribe_customer` DROP FOREIGN KEY FK_ITEMS_RELATION_ITEM ;
ALTER TABLE `campaign_raffle_online_subcrible` DROP FOREIGN KEY FK_RAFFLE_CUSTOMER ;
ALTER TABLE `campaign_raffle_online_products` DROP FOREIGN KEY FK_RAFFLE_PRODUCT ;
ALTER TABLE `campaign_products` DROP FOREIGN KEY FK_PRODUCT_RELATION_CAMPAIGN ;
");
$installer->endSetup();
