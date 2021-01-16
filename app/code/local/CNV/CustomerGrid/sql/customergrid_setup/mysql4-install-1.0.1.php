<?php
/**
 * @author MarginFrame Team
 * @copyright Copyright (c) 2015 MarginFrame (http://www.marginframe.com)
 * @package MarginFrame_Customerattr
 */

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `customer_entity`
ADD COLUMN `firstname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER `m_token`,
ADD COLUMN `lastname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER `firstname`,
ADD COLUMN `vip_member_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER `lastname`;

ALTER TABLE `customer_entity`
ADD INDEX `IDX_CUSTOMER_ENTITY_FIRSTNAME` (`firstname`) USING BTREE,
ADD INDEX `IDX_CUSTOMER_ENTITY_LASTNAME` (`lastname`) USING BTREE,
ADD INDEX `IDX_CUSTOMER_ENTITY_VIP_MEMBER_ID` (`vip_member_id`) USING BTREE;

UPDATE eav_attribute SET backend_type = 'static' WHERE attribute_code = 'firstname' AND entity_type_id = '1';
UPDATE eav_attribute SET backend_type = 'static' WHERE attribute_code = 'lastname' AND entity_type_id = '1';
UPDATE eav_attribute SET backend_type = 'static' WHERE attribute_code = 'vip_member_id' AND entity_type_id = '1';

");

$installer->endSetup();