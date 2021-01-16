<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattach
 */


if (Mage::helper('core')->isModuleEnabled('Amasty_Deliverydate')) {
    $autoloader = Varien_Autoload::instance();
    $autoloader->autoload('Amasty_Orderattach_Model_Sales_Order_AmastyDeliverydate');
} elseif (Mage::helper('core')->isModuleEnabled('AdjustWare_Deliverydate')) {
    $autoloader = Varien_Autoload::instance();
    $autoloader->autoload('Amasty_Orderattach_Model_Sales_Order_AdjustWareDeliverydate');
} else {
    class Amasty_Orderattach_Model_Sales_Order_Pure extends Mage_Sales_Model_Order {}
}
