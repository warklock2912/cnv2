<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */
$this->startSetup();

$this->run("
ALTER TABLE {$this->getTable('amgroupcat/rules')} CHANGE `price_on_product_view` `price_on_product_view` INT UNSIGNED NULL DEFAULT '0';
ALTER TABLE {$this->getTable('amgroupcat/rules')} CHANGE `price_on_product_list` `price_on_product_list` INT UNSIGNED NULL DEFAULT '0';
"
);

$this->endSetup();
