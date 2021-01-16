<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Payrestriction
 */
$this->startSetup();

$this->run("
ALTER TABLE `{$this->getTable('ampayrestriction/rule')}`  ADD `time_to` int(11) DEFAULT NULL AFTER `days`;
");

$this->run("
ALTER TABLE `{$this->getTable('ampayrestriction/rule')}`  ADD `time_from` int(11) DEFAULT NULL AFTER `days`;
");

$this->endSetup();