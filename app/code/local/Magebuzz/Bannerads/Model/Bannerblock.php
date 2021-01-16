<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Model_Bannerblock extends Mage_Core_Model_Abstract {
  public function _construct() {
    parent::_construct();
    $this->_init('bannerads/bannerblock');
  }

  public function saveBlock($blockIds, $bannerId) {
    $blockCollection = $this->getCollection()->addFieldToFilter('banner_id', $bannerId)->getData();
    $blockIdsOld = array();
    foreach ($blockCollection as $block) {
      $blockIdsOld[] = $block['block_id'];
    }
    $insert = array_diff($blockIds, $blockIdsOld);
    $delete = array_diff($blockIdsOld, $blockIds);
    $resource = Mage::getSingleton('core/resource');
    $writeConnection = $resource->getConnection('core_write');
    $tableBlockEntity = Mage::getSingleton('core/resource')->getTableName('bannerads_block_image_entity');
    if (count($delete) > 0) {
      foreach ($delete as $del) {
        $where = $tableBlockEntity . '.banner_id = ' . $bannerId . ' AND ' . $tableBlockEntity . '.block_id = ' . $del;
        $writeConnection->delete($tableBlockEntity, $where);
      }
    }
    if (count($insert) > 0) {
      $data = array();
      foreach ($insert as $blockInsert) {
        $data[] = array('banner_id' => $bannerId, 'block_id' => $blockInsert,);
      }
      if (count($data) > 0) {
        $writeConnection->insertMultiple($tableBlockEntity, $data);
      }
    }
  }
}