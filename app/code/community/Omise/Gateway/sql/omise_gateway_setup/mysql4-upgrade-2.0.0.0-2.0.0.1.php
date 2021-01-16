<?php

$installer = $this;

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->addAttribute("customer", "customer_card_token",  array(
    "type"     => "varchar",
    "backend"  => "",
    "label"    => "Customer Card Token",
    "input"    => "text",
    "source"   => "",
    "visible"  => true,
    "required" => false,
    "default"  => "",
    "frontend" => "",
    "unique"   => false,
    "note"     => "Customer Card Token"
));

$installer->endSetup();
