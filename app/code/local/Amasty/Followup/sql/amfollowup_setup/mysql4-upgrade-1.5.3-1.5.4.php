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
    CHANGE COLUMN `cancel_event_type` `cancel_event_type` varchar(255) DEFAULT NULL;
");

$this->endSetup(); 