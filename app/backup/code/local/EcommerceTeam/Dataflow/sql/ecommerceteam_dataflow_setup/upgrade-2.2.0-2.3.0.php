<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.0.5
 */

/** @var $this Mage_Catalog_Model_Resource_Setup */
$this->startSetup();

$profileTable = $this->getTable('ecommerceteam_dataflow/profile_import');

$sql = "ALTER TABLE {$profileTable} ADD COLUMN `update_existing` tinyint (1)  NOT NULL  after `scope`;";

$this->run($sql);

$this->endSetup();