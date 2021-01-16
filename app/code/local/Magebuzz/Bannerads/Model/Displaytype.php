<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Model_Displaytype extends Varien_Object {
  const TYPE_ALL = 1;
  const TYPE_RANDOM = 2;
  const TYPE_SLIDER = 3;
  const TYPE_SLIDER_WITH_DESC = 4;
  const TYPE_FADE = 5;
  const TYPE_FADE_WITH_DESC = 6;

  static public function getOptionArray() {
    return array(
      self::TYPE_ALL => Mage::helper('bannerads')->__('All Images'),
      self::TYPE_RANDOM => Mage::helper('bannerads')->__('Random'),
      self::TYPE_SLIDER => Mage::helper('bannerads')->__('Slider'),
      self::TYPE_SLIDER_WITH_DESC => Mage::helper('bannerads')->__('Slider with description'),
      self::TYPE_FADE => Mage::helper('bannerads')->__('Fade'),
      self::TYPE_FADE_WITH_DESC => Mage::helper('bannerads')->__('Fade with description'),
    );
  }
}