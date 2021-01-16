<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Block_Adminhtml_Reports_Renderer_Position extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
  public function render(Varien_Object $row) {
    $banner_id = $row->getBannerId();
    $html = '';
    $bannerBlock = Mage::getModel('bannerads/bannerblock')->getCollection()->addFieldToFilter('banner_id', $banner_id);
    $count = $bannerBlock->count();
    if ($count > 0) {
      $html .= '<dl>';
      foreach ($bannerBlock as $blockId) {
        $blockModel = Mage::getModel('bannerads/bannerads')->load($blockId->getBlockId());
        $html .= '<dd> + ' . $blockModel->getBlockPosition() . '</dd>';
      }
      $html .= '</dl>';
    }
    return $html;
  }
}