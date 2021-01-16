<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/

class Magebuzz_Dealerlocator_Model_Mysql4_Dealerlocator extends Mage_Core_Model_Mysql4_Abstract {
  public function _construct() {
    $this->_init('dealerlocator/dealerlocator', 'dealerlocator_id');
  }

  protected function _afterSave(Mage_Core_Model_Abstract $object) {
    $oldStores = $this->lookupStoreIds($object->getId());
    $oldTag = $this->lookupDealerTag($object->getId());
    $newStores = (array)$object->getStores();
    $newTag = (array)$object->getDealerTag();
    if (empty($newTag)) {
      $newTag = array();
    }
    if (empty($newStores)) {
      $newStores = (array)$object->getStoreId();
    }

    $table = $this->getTable('dealerlocator/dealerlocator_store');
    $insert = array_diff($newStores, $oldStores);
    $delete = array_diff($oldStores, $newStores);

    if ($delete) {
      $where = array('dealer_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete);

      $this->_getWriteAdapter()->delete($table, $where);
    }

    if ($insert) {
      $data = array();
      foreach ($insert as $storeId) {
        $data[] = array('dealer_id' => (int)$object->getId(), 'store_id' => (int)$storeId);
      }
      $this->_getWriteAdapter()->insertMultiple($table, $data);
    }

    // update tag by dealer Id    
    $tableTag = $this->getTable('dealerlocator/dealerlocator_tag');
    $insertTag = array_diff($newTag, $oldTag);
    $deleteTag = array_diff($oldTag, $newTag);

    if ($deleteTag) {
      $whereTag = array('dealer_id = ?' => (int)$object->getId(), 'tag IN (?)' => $deleteTag);

      $this->_getWriteAdapter()->delete($tableTag, $whereTag);
    }

    if ($insertTag) {
      $dataTag = array();

      foreach ($insertTag as $tag) {
        $dataTag[] = array('dealer_id' => (int)$object->getId(), 'tag' => $tag);
      }

      $this->_getWriteAdapter()->insertMultiple($tableTag, $dataTag);
    }

    // end update tag

    return parent::_afterSave($object);
  }

  protected function _afterLoad(Mage_Core_Model_Abstract $object) {
    if ($object->getId()) {
      $stores = $this->lookupStoreIds($object->getId());
      $tag = $this->lookupDealerTag($object->getId());
      $object->setData('store_id', $stores);
      $object->setData('dealer_tag', $tag);
    }
    return parent::_afterLoad($object);
  }

  public function lookupStoreIds($dealer_id) {
    $adapter = $this->_getReadAdapter();

    $select = $adapter->select()->from($this->getTable('dealerlocator/dealerlocator_store'), 'store_id')->where('dealer_id = ?', (int)$dealer_id);

    return $adapter->fetchCol($select);
  }

  public function lookupDealerTag($dealer_id) {
    $adapter = $this->_getReadAdapter();

    $select = $adapter->select()->from($this->getTable('dealerlocator/dealerlocator_tag'), 'tag')->where('dealer_id = ?', (int)$dealer_id);

    return $adapter->fetchCol($select);
  }

}