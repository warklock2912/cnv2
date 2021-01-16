<?php

/*
 * Copyright (c) 2013 www.magebuzz.com 
 */

class Magebuzz_Countingdown_Helper_Data extends Mage_Core_Helper_Abstract {

  function getCurrentTimestamp() {
    $now = date(Varien_Date::DATETIME_PHP_FORMAT, Mage::getModel('core/date')->timestamp(time()));
    return Mage::getModel('core/date')->timeStamp($now);
  }

  function getStartTime($category) {
    $_startTime = $category->getCountingDowns();
//    $a2 = explode('/', $_startTime);
//    $_startTime = $a2[1] . '/' . $a2[0] . '/' . $a2[2];
    return $_startTime;
  }

  function getCategorys() {

    $categorys = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('counting_down_category', '1')
            ->setOrder('counting_downs', 'asc');
    return $categorys;
  }

  function getTimecommingup() {
    
    $commingup = 0;
    $categorys = Mage::helper('countingdown')->getCategorys();
    $product_id = Mage::registry('current_product')->getId();
    $product = Mage::getModel('catalog/product')->load($product_id);
    $cats = $product->getCategoryIds();

    $_currentTimestamp = $this->getCurrentTimestamp();
    foreach ($categorys as $category) {

      $_startTimeStamp = Mage::getModel('core/date')->timestamp($this->getStartTime($category));
      $categoryarrray = array();
      $categoryEntityId = $category->getEntityId();

      if ($_startTimeStamp) {
        for ($i = 0; $i < count($cats); $i++) {
          if ($categoryEntityId == $cats[$i]) {
            $commingup = $_startTimeStamp;
            break;
          }
        }
      }
    }
    $countingup = $commingup - $_currentTimestamp;

    return $countingup;
  }
  function getTimeCommingupAddToCart($product_id) {

    $commingup = 0;
    $categorys = Mage::helper('countingdown')->getCategorys();
    $product = Mage::getModel('catalog/product')->load($product_id);
    $cats = $product->getCategoryIds();

    $_currentTimestamp = $this->getCurrentTimestamp();
    foreach ($categorys as $category) {

      $_startTimeStamp = Mage::getModel('core/date')->timestamp($this->getStartTime($category));
      $categoryarrray = array();
      $categoryEntityId = $category->getEntityId();

      if ($_startTimeStamp) {
        for ($i = 0; $i < count($cats); $i++) {
          if ($categoryEntityId == $cats[$i]) {
            $commingup = $_startTimeStamp;
            break;
          }
        }
      }
    }
    $countingup = $commingup - $_currentTimestamp;

    return $countingup;
  }

}
