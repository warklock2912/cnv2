<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SecurityAuth
 */


class Amasty_SecurityAuth_Model_Mysql4_Auth extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('amsecurityauth/user_auth', 'user_id');
    }

    /**
     * @param array $data
     *
     * @return int
     */
    public function insert(array $data)
    {
        return $this->_getWriteAdapter()->insert($this->getMainTable(), $data);
    }
}
