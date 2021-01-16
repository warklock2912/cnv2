<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `campaign` MODIFY `start_register_time` TIMESTAMP ;
ALTER TABLE `campaign` MODIFY `end_register_time` TIMESTAMP ;
");
$installer->endSetup();
