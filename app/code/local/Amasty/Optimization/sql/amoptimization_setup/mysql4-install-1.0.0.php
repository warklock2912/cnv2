<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


$this->startSetup();

$this->run("CREATE TABLE IF NOT EXISTS `{$this->getTable('amoptimization/task')}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `minificator_code` varchar(12) NOT NULL,
  `path` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `path` (`path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$this->endSetup();
