<?php
$installer = $this;
$installer->startSetup();
$installer->run("
	ALTER TABLE {$this->getTable('dealerlocator')} ADD `store_image_mobile` varchar(255) null;	
");
$installer->endSetup(); 