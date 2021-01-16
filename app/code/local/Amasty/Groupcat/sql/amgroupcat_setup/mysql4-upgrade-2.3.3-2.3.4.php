<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */
$this->startSetup();

$this->run("
  ALTER TABLE {$this->getTable('amgroupcat/rules')}  ADD `category_restricted_type` tinyint(1) unsigned default '0';
"
);

$session = Mage::getSingleton('admin/session');
$session->setReloadAclFlag(true);
$session->refreshAcl();

$this->endSetup();
