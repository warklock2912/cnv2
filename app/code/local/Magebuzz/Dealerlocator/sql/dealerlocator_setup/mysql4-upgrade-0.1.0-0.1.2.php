<?php
$installer = $this;
$installer->startSetup();
$installer->run("
	-- DROP TABLE IF EXISTS {$this->getTable('dealerlocator_store')};
	CREATE TABLE {$this->getTable('dealerlocator_store')} (
	  `dealer_id` int(11) unsigned NOT NULL,
		`store_id` int(11) unsigned NOT NULL,
		PRIMARY KEY (`dealer_id`,`store_id`),
		
		CONSTRAINT `FK_DEALERLOCATOR_STORE_DEALERLOCATOR` FOREIGN KEY (`dealer_id`) REFERENCES `{$this->getTable('dealerlocator')}` (`dealerlocator_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	  CONSTRAINT `FK_DEALERLOCATOR_STORE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");
$installer->endSetup(); 