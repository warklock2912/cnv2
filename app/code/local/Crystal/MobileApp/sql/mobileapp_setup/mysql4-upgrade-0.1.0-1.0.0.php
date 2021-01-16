<?php

$installer = $this;
$installer->startSetup();

$entity = $installer->getEntityTypeId('customer');

$attributeCode = 'mobileapp_ios_token';
$attributeName = 'MobileApp IOS Token';
$attributeForm = array('adminhtml_customer');
// $attributeForm = array('customer_account_edit', 'customer_account_create', 'adminhtml_customer', 'checkout_register');

$installer->addAttribute($entity, $attributeCode, array(
    'type' => 'text',
    'label' => $attributeName,
    'input' => 'text',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => '',
    'adminhtml_only' => '0'
));

$attribute = Mage::getSingleton('eav/config')->getAttribute($installer->getEntityTypeId('customer'), $attributeCode);
$attribute->setData('used_in_forms', $attributeForm);
$attribute->save();

$installer->endSetup();
