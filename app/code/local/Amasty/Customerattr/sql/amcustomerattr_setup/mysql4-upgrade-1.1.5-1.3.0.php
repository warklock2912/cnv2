<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */

$installer = $this;

$installer->startSetup();

try {
    $installer->run(
        "

ALTER TABLE `{$this->getTable('customer/eav_attribute')}` ADD `type_internal`  VARCHAR( 255 ) NOT NULL ;

"
    );
} catch (Exception $e) {
    // do nothing. this field may already present in some older Magento versions
}

$installer->endSetup(); 