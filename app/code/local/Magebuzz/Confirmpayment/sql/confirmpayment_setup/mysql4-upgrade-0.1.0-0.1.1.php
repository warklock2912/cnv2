<?php

$installer = $this;
$installer->startSetup();

$installer->run("

ALTER TABLE `{$this->getTable('cp_form_submit')}`
  ADD COLUMN `attachment` varchar (255) NOT NULL default '' AFTER `message` ;
");

$installer->endSetup();