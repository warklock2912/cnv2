<?php
$installer = $this;
$installer->startSetup();

$installer->run("
  ALTER TABLE {$this->getTable('ruffle')} ADD `am_table_method_id` int(1) NULL ;
  ALTER TABLE {$this->getTable('ruffle_joiner')} ADD `storepickup_id` int(1) unsigned NOT NULL DEFAULT '0';  
  ALTER TABLE {$this->getTable('ruffle_joiner')} ADD `country_id` varchar(250) NOT NULL DEFAULT 'TH'; 
  ALTER TABLE {$this->getTable('ruffle_joiner')} ADD `region_id` int(11) unsigned; 
  ALTER TABLE {$this->getTable('ruffle_joiner')} ADD `city_id` int(11) unsigned;
  ALTER TABLE {$this->getTable('ruffle_joiner')} ADD `subdistrict_id` int(11) unsigned; 
  ALTER TABLE {$this->getTable('ruffle_joiner')} ADD `postcode` varchar(250) NULL;
");

$installer->endSetup();
