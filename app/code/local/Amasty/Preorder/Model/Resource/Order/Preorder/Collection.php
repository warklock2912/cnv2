<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */
class Amasty_Preorder_Model_Resource_Order_Preorder_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('ampreorder/order_preorder');
    }
}