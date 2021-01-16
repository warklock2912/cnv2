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
    ALTER TABLE `{$this->getTable('eav/attribute_option')}` ADD `group_id` INT( 10 )  UNSIGNED NOT NULL ; 
"
);

$installer->endSetup(); 