<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Block_Adminhtml_Images_Renderer_Images extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
  public function render(Varien_Object $row) {
    $bannerId = $row->getBannerImage();
    $html = '';
    if ($bannerId != '') {
      $imgPath = Mage::getBaseUrl('media') . "banners/images/" . $bannerId;
      $html = '<img src="' . $imgPath . '" alt=" ' . $imgPath . '" height="80" width="80" />';
    }
    return $html;
  }
}