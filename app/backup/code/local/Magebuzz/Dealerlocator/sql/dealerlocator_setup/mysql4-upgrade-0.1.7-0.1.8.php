<?php
$installer = $this;
$installer->startSetup();
$installer->run("
  -- DROP TABLE IF EXISTS {$this->getTable('dealerlocator_tag')};
  CREATE TABLE {$this->getTable('dealerlocator_tag')} (    
    `dealer_tag_id` int(11) unsigned NOT NULL auto_increment,
    `dealer_id` int(11) unsigned NOT NULL, 
    `tag` varchar(255) NULL,
    PRIMARY KEY (`dealer_tag_id`),  
    CONSTRAINT `FK_DEALERLOCATOR_TAG_DEALERLOCATOR` FOREIGN KEY (`dealer_id`) REFERENCES `{$this->getTable('dealerlocator')}` (`dealerlocator_id`) ON UPDATE CASCADE ON DELETE CASCADE 
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup(); 