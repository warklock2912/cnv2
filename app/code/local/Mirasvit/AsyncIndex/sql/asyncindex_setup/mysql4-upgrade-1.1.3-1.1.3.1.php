<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_asyncindex
 * @version   1.1.13
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


$installer = $this;
$installer->startSetup();

$installer->getConnection()->delete($this->getTable('index/event'));
$installer->getConnection()->delete($this->getTable('index/process_event'));

if ($installer->tableExists($this->getTable('mstcore/logger'))) {
    $installer->run("
        TRUNCATE TABLE {$this->getTable('mstcore/logger')};
    ");
}

$installer->endSetup();