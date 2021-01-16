<?php
class Tigren_Member_Model_Mysql4_Notify_Vip extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('member/notify_vip', 'entity_id');
    }
}