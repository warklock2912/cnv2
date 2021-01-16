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

ALTER TABLE `{$this->getTable('sales/quote_address')}` ADD `same_as_shipping` smallint unsigned default '0';

"
);

$installer->endSetup(); 