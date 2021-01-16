<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('amfollowup/schedule')}` 
    ADD COLUMN `discount_qty` INT(5) UNSIGNED after expired_in_days,
    ADD COLUMN `discount_step` INT(5) UNSIGNED after discount_qty,
    ADD COLUMN `promo_sku` VARCHAR(255) default null after discount_step,
    ADD COLUMN `promo_cats` VARCHAR(255) default null after promo_sku,
    CHANGE COLUMN `coupon_type` `coupon_type` VARCHAR(255) default null
");

$this->endSetup(); 