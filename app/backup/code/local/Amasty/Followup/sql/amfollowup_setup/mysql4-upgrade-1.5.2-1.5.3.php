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
    ADD COLUMN `ampromo_type` VARCHAR(255) default '0' after promo_cats;
");

$this->endSetup(); 