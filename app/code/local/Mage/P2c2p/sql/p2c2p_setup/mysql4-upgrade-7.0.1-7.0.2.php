<?php
/** @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;
$installer->startSetup();
$entity = $installer->getEntityTypeId('customer');



$installer->addAttribute($entity, 'card_token_2c2p', array(
    'type' => 'text',
    'label' => 'Card Token 2c2p',
    'input' => 'text',
    'visible' => TRUE,
    'required' => FALSE,
    'default_value' => '',
    'adminhtml_only' => '0'
));
$forms = array(
    'adminhtml_customer',
);
$attribute = Mage::getSingleton('eav/config')->getAttribute($installer->getEntityTypeId('customer'), 'card_token_2c2p');
$attribute->setData('used_in_forms', $forms);
$attribute->save();

$installer->endSetup();