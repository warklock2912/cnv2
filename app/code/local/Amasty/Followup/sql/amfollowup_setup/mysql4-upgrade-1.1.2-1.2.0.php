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

ALTER TABLE `{$this->getTable('amfollowup/history')}` 
ADD COLUMN `coupon_to_date` DATE DEFAULT NULL AFTER coupon_code;

");

$this->endSetup(); 