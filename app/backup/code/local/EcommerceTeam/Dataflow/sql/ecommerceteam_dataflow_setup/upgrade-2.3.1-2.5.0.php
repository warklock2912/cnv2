<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2016 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.5.0
 */

/** @var $this Mage_Catalog_Model_Resource_Setup */
$this->startSetup();

$profileTable = $this->getTable('ecommerceteam_dataflow/profile_import');

$sql = "ALTER TABLE {$profileTable} ADD COLUMN `transform` TEXT  NOT NULL;";
$sql .= "ALTER TABLE {$profileTable} ADD COLUMN `fallbacks` TEXT  NOT NULL;";

$this->run($sql);

$this->endSetup();