<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/

class Magebuzz_Dealerlocator_Helper_Data extends Mage_Core_Helper_Abstract {
  public function getJsonData($address) {
    $storeId = Mage::app()->getStore()->getId();
    $configUrl = Mage::getStoreConfig('dealerlocator/google_map_options/google_geo_api_url', $storeId);
    if ($configUrl != '') {
      $url = Mage::getStoreConfig('dealerlocator/google_map_options/google_geo_api_url') . "?address=$address";
    } else {
      $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address;
    }

    $rCURL = curl_init();
    curl_setopt($rCURL, CURLOPT_URL, $url);
    curl_setopt($rCURL, CURLOPT_HEADER, 0);
    curl_setopt($rCURL, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
    curl_setopt($rCURL, CURLOPT_RETURNTRANSFER, 1);
    $aData = curl_exec($rCURL);
    curl_close($rCURL);
    $json = json_decode($aData);
    return $json;
  }

  public function showTopLink() {
    return Mage::getStoreConfig('dealerlocator/google_map_options/show_top_link');
  }

  public function getDefaultAddress() {
    $storeId = Mage::app()->getStore()->getId();
    return Mage::getStoreConfig('dealerlocator/google_map_options/default_location_address', $storeId);
  }

  public function showEmailAndWebsite() {
    $storeId = Mage::app()->getStore()->getId();
    return Mage::getStoreConfig('dealerlocator/google_map_options/show_email_and_website', $storeId);
  }

  public function getSearchRadius() {
    $storeId = Mage::app()->getStore()->getId();
    return Mage::getStoreConfig('dealerlocator/google_map_options/default_search_radius', $storeId);
  }

  public function getRadiusUnit() {
    $storeId = Mage::app()->getStore()->getId();
    return Mage::getStoreConfig('dealerlocator/google_map_options/distance_units', $storeId);
  }

  public function getSearchRadiusOptions() {
    $storeId = Mage::app()->getStore()->getId();
    $radius = Mage::getStoreConfig('dealerlocator/google_map_options/search_radius', $storeId);
    $options = array();
    if ($radius != '') {
      $radius_array = explode(',', $radius);;
      if (count($radius_array)) {
        foreach ($radius_array as $item) {
          $options[] = array('value' => trim($item), 'label' => $this->getSearchLabel(trim($item)));
        }
      }
    }
    return $options;
  }

  public function getSearchLabel($radius) {
    $unit = $this->getRadiusUnit();
    $searchLabel = '';
    if ($unit == 1) {
      $searchLabel = $radius . ' Kilometers';
    } else {
      $searchLabel = $radius . ' Miles';
    }
    return $searchLabel;
  }

  public function renameImage($image_name) {
    $string = str_replace("  ", " ", $image_name);
    $new_image_name = str_replace(" ", "-", $string);
    $new_image_name = strtolower($new_image_name);
    return $new_image_name;
  }

  public function getDefaultDealerIcon() {
    $storeId = Mage::app()->getStore()->getId();
    return (string)Mage::getStoreConfig('dealerlocator/google_map_options/default_dealer_icon', $storeId);
  }

  public function enableSearchSuggestion() {
    $storeId = Mage::app()->getStore()->getId();
    return (bool)Mage::getStoreConfig('dealerlocator/google_map_options/enable_search_suggestion', $storeId);
  }

  public function displaySearchForm() {
    $storeId = Mage::app()->getStore()->getId();
    return (bool)Mage::getStoreConfig('dealerlocator/google_map_options/show_search_form', $storeId);
  }

  public function getPageType() {
    $storeId = Mage::app()->getStore()->getId();
    return (int)Mage::getStoreConfig('dealerlocator/google_map_options/page_type', $storeId);
  }

  public function getShowPerPageOptions() {
    $storeId = Mage::app()->getStore()->getId();
    $options = array();
    $showPerPage = Mage::getStoreConfig('dealerlocator/google_map_options/show_per_page_values', $storeId);
    if ($showPerPage != '') {
      $showPerPageOptions = explode(',', $showPerPage);
      if (!empty($showPerPageOptions)) {
        foreach ($showPerPageOptions as $option) {
          $options[$option] = $option;
        }
      }
    }
    return $options;
  }

  public function getDefaultShowPerPage() {
    $storeId = Mage::app()->getStore()->getId();
    return (int)Mage::getStoreConfig('dealerlocator/google_map_options/show_per_page', $storeId);
  }

  /* update ver 1.8 add tag for deal
   * filter Dealer by tag
   * retun list dealer
   */

  public function filterDealerByTag($tag) {
    $TagModel = Mage::getModel('dealerlocator/tag')->getCollection();
    $TagModel->addFieldToFilter('tag', array('like' => '%' . $tag . '%'));
    $TagModel->getSelect()->group('dealer_id');
    $lisTag = $TagModel->getColumnValues('dealer_id');
    return $lisTag;
  }

  public function canShowTagFilter() {
    return Mage::getStoreConfig("dealerlocator/google_map_options/display_tag_filter");
  }

  public function distanceBetweenTwoCoord($lat1, $lon1, $lat2, $lon2) {
    if ($lat1 && $lon1 && $lat2 && $lon2) {
      $unit = Mage::helper('dealerlocator')->getRadiusUnit();
      $theta = $lon1 - $lon2;
      $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
      $dist = acos($dist);
      $dist = rad2deg($dist);
      $miles = $dist * 60 * 1.1515;
      if ($unit == 1) {
        return ($miles * 1.609344);
      } else {
        return $miles;
      }
    } else return 0;
  }

  public function sortByValue(&$array, $key) {
    $sorter = array();
    $ret = array();
    reset($array);
    foreach ($array as $ii => $va) {
      $sorter[$ii] = $va[$key];
    }
    asort($sorter);
    foreach ($sorter as $ii => $va) {
      $ret[$ii] = $array[$ii];
    }
    $array = $ret;
  }
}