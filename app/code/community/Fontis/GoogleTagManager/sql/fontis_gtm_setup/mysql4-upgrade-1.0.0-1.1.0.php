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
 * @copyright  Copyright (c) 2014 Fontis Pty. Ltd. (http://www.fontis.com.au)
 * @license    Fontis Software License
 */

$installer = $this;
$installer->startSetup();

$installer->run("
UPDATE {$this->getTable('core_config_data')}
SET path = REPLACE(path, 'google/googletagmanager/', 'fontis_gtm/settings/')
WHERE path LIKE 'google/googletagmanager/%';
");

$installer->endSetup();
