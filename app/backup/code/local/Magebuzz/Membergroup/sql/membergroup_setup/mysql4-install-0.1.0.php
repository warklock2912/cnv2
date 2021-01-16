<?php
$installer = $this;
$installer->startSetup();


$installer->addAttribute("customer", "vip_member_status",  array(
  "type"     => "text",
  "backend"  => "",
  "label"    => "Vip Member Status",
  "input"    => "select",
  "source"   => "membergroup/eav_entity_attribute_source_membergroupoptions",
  "visible"  => true,
  "required" => false,
  "default" => "",
  "frontend" => "",
  "unique"     => false,
  "note"       => ""

));

$attribute   = Mage::getSingleton("eav/config")->getAttribute("customer", "vip_member_status");


$used_in_forms=array();

$used_in_forms[]="adminhtml_customer";
$attribute->setData("used_in_forms", $used_in_forms)
  ->setData("is_used_for_customer_segment", true)
  ->setData("is_system", 0)
  ->setData("is_user_defined", 1)
  ->setData("is_visible", 1)
  ->setData("sort_order", 100)
;
$attribute->save();



$installer->endSetup();