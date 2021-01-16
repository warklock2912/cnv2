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

$this->getConnection()->addColumn(
    $profileTable,
    'scope',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'default'   => 0,
        'nullable'  => false,
        'comment'   => 'Store View Scope'
    )
);

$this->endSetup();