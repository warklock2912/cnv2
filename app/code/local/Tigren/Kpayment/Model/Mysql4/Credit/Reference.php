<?php
class Tigren_Kpayment_Model_Mysql4_Credit_Reference extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('kpayment/credit_reference', 'entity_id');
    }

    public function loadByChargeId(Mage_Core_Model_Abstract $object, $chargeId)
    {
        $connection = $this->getReadConnection();

        if ($connection && $chargeId !== null) {
            $select = $connection->select()->from(
                $this->getMainTable(),
                '*'
            )->where($this->getMainTable() . '.charge_id = ?', $chargeId);

            $data = $connection->fetchRow($select);

            if ($data) {
                $object->setData($data);
            }
        }

        $this->_afterLoad($object);

        return $this;
    }
}