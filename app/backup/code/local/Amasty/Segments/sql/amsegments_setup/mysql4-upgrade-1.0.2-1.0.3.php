<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */
$this->startSetup();



$this->run("
    ALTER TABLE `{$this->getTable('amsegments/segment')}`
    CHANGE COLUMN `website_id` `website_ids` VARCHAR(255) DEFAULT '';
");


$this->endSetup();