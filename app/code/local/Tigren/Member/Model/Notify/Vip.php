<?php
class Tigren_Member_Model_Notify_Vip extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('member/notify_vip');
    }
}