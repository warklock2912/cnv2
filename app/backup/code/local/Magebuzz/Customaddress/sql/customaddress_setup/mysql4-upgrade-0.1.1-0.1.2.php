<?php
$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $setup->getEntityTypeId('customer_address');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$setup->addAttribute('customer_address', 'city_id', array(
    'input'         => 'hidden',
    'type'          => 'int',
    'label'         => 'City ID',
    'visible'       => 1,
    'required'      => 0, 
    'user_defined' => 0,
));

$setup->addAttribute('customer_address', 'subdistrict_id', array(
    'input'         => 'hidden',
    'type'          => 'int',
    'label'         => 'Subdistrict ID',
    'visible'       => 1,
    'required'      => 0, 
    'user_defined' => 0,
));


$setup->addAttribute('customer_address', 'subdistrict', array(
    'input'         => 'text',
    'type'          => 'varchar',
    'label'         => 'Subdistrict',
    'visible'       => 1,
    'required'      => 0, 
    'user_defined' => 0,
));

$oAttribute = Mage::getSingleton('eav/config')->getAttribute('customer_address', 'city_id');
$oAttribute->setData('used_in_forms', array('customer_register_address','customer_address_edit','adminhtml_customer_address'));
$oAttribute->save();

$oAttribute = Mage::getSingleton('eav/config')->getAttribute('customer_address', 'subdistrict_id');
$oAttribute->setData('used_in_forms', array('customer_register_address','customer_address_edit','adminhtml_customer_address'));
$oAttribute->save();

$oAttributea = Mage::getSingleton('eav/config')->getAttribute('customer_address', 'subdistrict');
$oAttributea->setData('used_in_forms', array('customer_register_address','customer_address_edit','adminhtml_customer_address'));
$oAttributea->save();


//Update attribute sort order
$attribute_region = Mage::getModel('eav/entity_attribute')->loadByCode('2', 'region');
$attribute_city = Mage::getModel('eav/entity_attribute')->loadByCode('2', 'city');
$attribute_subdistrict = Mage::getModel('eav/entity_attribute')->loadByCode('2', 'subdistrict');
$table_name = Mage::getSingleton('core/resource')->getTableName('customer_eav_attribute');

/* region, city, subdistrict */
$installer->run("
	UPDATE {$table_name} SET `sort_order`= 103 WHERE `attribute_id` = {$attribute_region->getId()};
	UPDATE {$table_name} SET `sort_order`= 104 WHERE `attribute_id` = {$attribute_city->getId()};
	UPDATE {$table_name} SET `sort_order`= 105 WHERE `attribute_id` = {$attribute_subdistrict->getId()}
");

$setup->endSetup();