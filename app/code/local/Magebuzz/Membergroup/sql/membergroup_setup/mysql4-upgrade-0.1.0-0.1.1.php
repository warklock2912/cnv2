<?php
$installer = $this;
$installer->startSetup();

$installer->addAttribute("customer", "updated_group_at",  array(
        'type'               => 'datetime',
        'label'              => 'Updated Customer Group At',
        'input'              => 'date',
        'frontend'           => 'eav/entity_attribute_frontend_datetime',
        'backend'            => 'eav/entity_attribute_backend_datetime',
        'required'           => false,
        'sort_order'         => 90,
        'visible'            => false,
        'system'             => false,
        'input_filter'       => 'date',
        'validate_rules'     => 'a:1:{s:16:"input_validation";s:4:"date";}'
        ));

$attribute   = Mage::getSingleton("eav/config")->getAttribute("customer", "updated_group_at");

$used_in_forms = array();
$used_in_forms[] = "adminhtml_customer";

$attribute->setData("used_in_forms", $used_in_forms)
    ->setData("is_used_for_customer_segment", true)
    ->setData("is_system", 0)
    ->setData("is_user_defined", 1)
    ->setData("is_visible", 1)
    ->setData("sort_order", 100)
;
$attribute->save();

$installer->endSetup();