<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Mageplace_FastProductUpdate
 */


class Mageplace_FastProductUpdate_Model_Stock extends Mage_CatalogInventory_Model_Stock_Item
{
    public function save()
    {
        if (Mage::getStoreConfig(Mageplace_FastProductUpdate_Helper_Const::SYSTEM_GENERAL_DIRECT_DB_IMPORT)) {
            $this->_getResource()->beginTransaction();
            try {
                $update = $this->_beforeSave()->getData();

                $update['is_in_stock'] = (int)$update['is_in_stock'];
                unset(
                    $update['stock_status_changed_automatically_flag'],
                    $update['type_id'],
                    $update['item_id'],
                    $update['product_id'],
                    $update['stock_id']
                );

                Mage::getSingleton('core/resource')->getConnection('core_write')->update(
                    $this->_getResource()->getMainTable(),
                    $update,
                    array('item_id = ?' => $this->getId())
                );

                $this->_getResource()->commit();
            } catch (Exception $e) {
                $this->_getResource()->rollBack();
                Mage::logException($e);
                throw $e;
            }

            return $this;
        } else {
            return parent::save();
        }
    }
}