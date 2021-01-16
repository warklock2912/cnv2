<?php
$installer = $this;
$installer->startSetup();
$installer->run("
	ALTER TABLE {$this->getTable('dealerlocator')} ADD `store_image` varchar(255) null;	
");
$installer->endSetup(); 