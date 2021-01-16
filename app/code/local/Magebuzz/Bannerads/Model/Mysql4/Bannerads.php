<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Model_Mysql4_Bannerads extends Mage_Core_Model_Mysql4_Abstract {
	protected $_categoryInstance = null;
  public function _construct() {
    $this->_init('bannerads/bannerads', 'block_id');
  }

  protected function _afterLoad(Mage_Core_Model_Abstract $object) {
    if ($object->getId()) {
      $stores = $this->lookupStoreIds($object->getId());
      $banners = $this->lookupImagesId($object->getId());
      $object->setData('store_id', $stores);
      $object->setData('banner_id', $banners);
    }
    return parent::_afterLoad($object);
  }

  public function getListBannerByBlockId($block_id) {
    $adapter = $this->_getReadAdapter();
    $select = $adapter->select()
      ->from($this->getTable('bannerads/banneradsstore'), 'store_id')
      ->where('block_id = ?', (int)$block_id);
    return $adapter->fetchCol($select);
  }

  protected function _afterSave(Mage_Core_Model_Abstract $object) {
    $oldStores = $this->lookupStoreIds($object->getId());
    $newStores = (array)$object->getStores();
    $oldbanners = $this->lookupImagesId($object->getId());
    $newbanners = (array)$object->getBannerId();

    if (empty($newbanners)) {
      $newbanners = (array)$object->getBannerId();
    }

    if (empty($newStores)) {
      $newStores = (array)$object->getStoreId();
    }
    $this->saveStore($newStores, $oldStores, $object->getId());
    $this->saveBannerId($newbanners, $oldbanners, $object->getId());

    return parent::_afterSave($object);
  }

  public function saveStore($newStores, $oldStores, $blockId) {
    $table = $this->getTable('bannerads/banneradsstore');
    $insert = array_diff($newStores, $oldStores);
    $delete = array_diff($oldStores, $newStores);
    if ($delete) {
      $where = array('block_id = ?' => (int)$blockId, 'store_id IN (?)' => $delete);
      $this->_getWriteAdapter()->delete($table, $where);
    }
    if ($insert) {
      $data = array();
      foreach ($insert as $storeId) {
        $data[] = array('block_id' => (int)$blockId, 'store_id' => (int)$storeId);
      }
      $this->_getWriteAdapter()->insertMultiple($table, $data);
    }
  }

  public function saveBannerId($newbanners, $oldbanners, $blockId) {
    $table = $this->getTable('bannerads/bannerblock');
    $insert = array_diff($newbanners, $oldbanners);
    $delete = array_diff($oldbanners, $newbanners);
    if ($delete) {
      $where = array('block_id = ?' => (int)$blockId, 'banner_id IN (?)' => $delete);
      $this->_getWriteAdapter()->delete($table, $where);
    }
    if ($insert) {
      $data = array();
      foreach ($insert as $bannerId) {
        $data[] = array('block_id' => (int)$blockId, 'banner_id' => (int)$bannerId);
      }
      $this->_getWriteAdapter()->insertMultiple($table, $data);
    }
  }

  public function lookupStoreIds($block_id) {
    $adapter = $this->_getReadAdapter();
    $select = $adapter->select()->from($this->getTable('bannerads/banneradsstore'), 'store_id')->where('block_id = ?', (int)$block_id);
    return $adapter->fetchCol($select);
  }

  public function lookupImagesId($block_id) {
    $ids = array();
    $images = Mage::getModel('bannerads/bannerblock')->getCollection()->addFieldToFilter('block_id', (int)$block_id);
    foreach ($images as $image) {
      $ids[] = $image->getBannerId();
    }
    return $ids;
  }


}
