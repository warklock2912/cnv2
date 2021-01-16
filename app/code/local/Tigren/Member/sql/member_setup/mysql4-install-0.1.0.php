<?php
$installer = $this;

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$setup->removeAttribute('customer','notified_member');

$setup->addAttribute("customer", "notified_member",  array(
    "type"     => "int",
    "backend"  => "",
    "label"    => "Notified Member",
    "input"    => "text",
    "source"   => "",
    "visible"  => true,
    "required" => false,
    "default" => "0",
    "frontend" => "",
    "unique"     => false,
    "note"       => "Notified Member"
));

$attribute   = Mage::getSingleton("eav/config")->getAttribute("customer", "notified_member");

$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'notified_member',
    '999'  //sort_order
);

$used_in_forms=array();

$used_in_forms[]="adminhtml_customer";
//$used_in_forms[]="checkout_register";
//$used_in_forms[]="customer_account_create";
//$used_in_forms[]="customer_account_edit";
//$used_in_forms[]="adminhtml_checkout";
$attribute->setData("used_in_forms", $used_in_forms)
    ->setData("is_used_for_customer_segment", true)
    ->setData("is_system", 0)
    ->setData("is_user_defined", 1)
    ->setData("is_visible", 1)
    ->setData("sort_order", 100)
;
$attribute->save();



$installer->endSetup();