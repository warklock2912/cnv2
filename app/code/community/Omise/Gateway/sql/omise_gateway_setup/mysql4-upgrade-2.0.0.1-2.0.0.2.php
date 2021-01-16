<?php

$installer = $this;

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->addAttribute("customer", "customer_api_id",  array(
    "type"     => "varchar",
    "backend"  => "",
    "label"    => "Customer Api Id",
    "input"    => "text",
    "source"   => "",
    "visible"  => true,
    "required" => false,
    "default"  => "",
    "frontend" => "",
    "unique"   => false,
    "note"     => "Customer Api Id"
));

$installer->endSetup();
