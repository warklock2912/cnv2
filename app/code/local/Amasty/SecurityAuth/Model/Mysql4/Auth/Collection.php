<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SecurityAuth
 */


class Amasty_SecurityAuth_Model_Mysql4_Auth_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('amsecurityauth/auth');
    }
}
