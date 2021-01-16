<?php

class Mage_P2c2p_Model_Mysql4_Token extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('p2c2p/token', 'p2c2p_id');
    }

    public function loadByCardUniqueToken(Mage_Core_Model_Abstract $object, $cardToken)
    {
        $adapter    = $this->_getReadAdapter();
        $select     = $adapter->select()->from($this->getMainTable());
        $select->where('stored_card_unique_id = ?', $cardToken);
        $row        = $adapter->fetchRow($select);
        if ($row && !empty($row)) {
            $object->setData($row);
        }
        $this->_afterLoad($object);
        return $this;
    }
}
