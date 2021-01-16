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
    ALTER TABLE `{$this->getTable('amfollowup/rule')}`
    ADD COLUMN `customer_date_event` DATE DEFAULT NULL;
");

Amasty_Followup_Helper_Template::create('amfollowup_customer_date', 'Amasty Follow Up Email: Merry Christmas');

$this->endSetup();