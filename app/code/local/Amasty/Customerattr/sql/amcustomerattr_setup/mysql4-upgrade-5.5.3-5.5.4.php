<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */

$installer = $this;

$installer->startSetup();

$installer->run(
    "
UPDATE `{$this->getTable('eav_attribute')}` `upd` ,
        (SELECT `eav`.`attribute_code`
         FROM `{$this->getTable('eav_attribute')}` AS `eav`
         INNER JOIN `{$this->getTable('customer/eav_attribute')}` AS `eav_cust`
         ON `eav`.`attribute_id`=`eav_cust`.`attribute_id`
         LEFT JOIN `{$this->getTable('customer/eav_attribute_website')}` AS `eav_web`
         ON `eav_web`.`attribute_id`=`eav`.`attribute_id`
         AND `eav_web`.`website_id`='0'
         WHERE (`eav`.`entity_type_id` = 1)
         AND (`eav`.`is_user_defined` = 1)
         AND (`eav`.`attribute_code` != 'customer_activated'))  `src`
SET `upd`.`frontend_model`='Amasty_Customerattr_Model_Rewrite_Eav_Entity_Attribute_Frontend_Default'
WHERE `upd`.`attribute_code` = `src`.`attribute_code`;
"
);


$installer->endSetup();