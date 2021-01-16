<?php

$installer = $this;
$installer->startSetup();
$installer->run(
    "

DROP TABLE IF EXISTS {$this->getTable('cp_form_submit')};
CREATE TABLE {$this->getTable('cp_form_submit')} (
  `form_id` int(11) unsigned NOT NULL auto_increment,
  `order_no` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `tel` varchar(255) NOT NULL default '',
  `amount` varchar(255) NOT NULL default '',
  `bank` varchar(255) NOT NULL default '',
  `message` varchar(255) NOT NULL default '',
  `date` datetime NULL,
  `status` smallint(6) NOT NULL default '1',
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

"
);
$installer->endSetup();