<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Fontis Software License that is available in
 * the FONTIS-LICENSE.txt file included with this extension. This file is located
 * by default in the root directory of your Magento installation. If you are unable
 * to obtain the license from the file, please contact us via our website and you
 * will be sent a copy.
 *
 * @category   Fontis
 * @copyright  Copyright (c) 2015 Fontis Pty. Ltd. (http://www.fontis.com.au)
 * @license    Fontis Software License
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$coreConfigTable = $this->getTable('core_config_data');

$adapter = $installer->getConnection();

$data = array('path' => 'fontis_gtm/settings/datalayerecommerce');
$where = array('path = ?' => 'fontis_gtm/settings/datalayertransactions');
$adapter->update($coreConfigTable, $data, $where);

$select = $adapter->select()
    ->from($coreConfigTable, array('scope', 'scope_id'))
    ->where('path = ?', 'fontis_gtm/settings/datalayerecommerce')
    ->where('value = ?', 1);
$transactionValues = $adapter->fetchAll($select);

foreach ($transactionValues as $transactionValue) {
    /* Previously, the only ecommerce (transaction) type available was standard.
    Therefore, if transaction data is enabled, we'll need to set the type to
    standard. */
    $installer->setConfigData('fontis_gtm/settings/datalayerecommercetype',
        Fontis_GoogleTagManager_Model_Source_EcommerceType::ECOMMERCE_TYPE_STANDARD,
        $transactionValue['scope'], $transactionValue['scope_id']);
}

$installer->endSetup();
