<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/

class Magebuzz_Dealerlocator_Block_Dealerlocator extends Mage_Core_Block_Template {
  protected function _construct() {
    parent::_construct();
    $pageType = Mage::helper('dealerlocator')->getPageType();
    if ($pageType == 2) {
      $this->setTemplate('dealerlocator/2columns.phtml');
    } else {
      $this->setTemplate('dealerlocator/dealerlocator.phtml');
    }
  }

  public function displaySearchForm() {
    return Mage::helper('dealerlocator')->displaySearchForm();
  }

  public function _prepareLayout() {
    parent::_prepareLayout();
    $showPerPageOptions = Mage::helper('dealerlocator')->getShowPerPageOptions();
    if (empty($showPerPageOptions)) {
      $showPerPageOptions = array(10 => 10, 20 => 20, 30 => 30);
    }
    $pager = $this->getLayout()->createBlock('page/html_pager', 'dealer.pager');
    $pager->setAvailableLimit($showPerPageOptions);
    $pager->setLimit(Mage::helper('dealerlocator')->getDefaultShowPerPage());
    $collection = $this->_getDealers();
    $pager->setCollection($collection);
    $this->setChild('pager', $pager);
    return $this;
  }

  protected function _getDealers() {
    $data = $this->getRequest()->getParams();
    $storeIds = array(Mage_Core_Model_App::ADMIN_STORE_ID, Mage::app()->getStore()->getId());
    $_collection = Mage::getModel('dealerlocator/dealerlocator')->getCollection();
    $_collection->getSelect()->join(array('dealer_store' => Mage::getModel('core/resource')->getTableName('dealerlocator_store')), 'main_table.dealerlocator_id = dealer_store.dealer_id')->where('dealer_store.store_id IN (?)', $storeIds)->where('status=?', 1);

    $centerLatitude = $_collection->getFirstItem()->getLatitude();
    $centerLongitude = $_collection->getFirstItem()->getLongitude();
    Mage::getSingleton('core/session')->setData('centerLatitude', $centerLatitude);
    Mage::getSingleton('core/session')->setData('centerLongitude', $centerLongitude);
    Mage::getSingleton('core/session')->setData('dealer_query', '');

    if (isset($data['q']) && $data['q'] != '') {
      if (isset($data['d'])) {
        $radius = $data['d'];
      } else {
        $radius = Mage::helper('dealerlocator')->getSearchRadius();
      }
      $dealerIds = array();
      if ($data['q']) {
        $address = urlencode($data['q']);
        $json = Mage::helper('dealerlocator')->getJsonData($address);
        $centerLatitude = strval($json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'});
        $centerLongitude = strval($json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'});
        foreach ($_collection as $item) {
          if (Mage::helper('dealerlocator')->distanceBetweenTwoCoord($item->getLatitude(), $item->getLongitude(), $centerLatitude, $centerLongitude) <= $radius) $dealerIds[] = $item->getId();
        }
      }
      $_collection->addFieldToFilter('dealerlocator_id', array('in' => $dealerIds));
      Mage::getSingleton('core/session')->setData('dealer_query', $data['q']);
      Mage::getSingleton('core/session')->setData('centerLatitude', $centerLatitude);
      Mage::getSingleton('core/session')->setData('centerLongitude', $centerLongitude);
      $_collection->addExpressionFieldToSelect('distance', '3956 * 2 * ASIN(SQRT( POWER(SIN(({{latitude}} - ' . $centerLatitude . ') *  pi()/180 / 2), 2) +COS({{latitude}} * pi()/180) * COS(' . $centerLatitude . ' * pi()/180) * POWER(SIN(({{longitude}} - ' . $centerLongitude . ') * pi()/180 / 2), 2) ))', array('latitude' => 'latitude', 'longitude' => 'longitude'));
      $_collection->getSelect()->order('distance', 'ASC');
    }

    if (isset($data['tag']) && $data['tag'] != '') {
      // filter collection by tag
      $listDealerIds = Mage::helper('dealerlocator')->filterDealerByTag($data['tag']);
      $_collection->addFieldToFilter('dealerlocator_id', array('in' => $listDealerIds));
    }

    if (isset($data['current_latitude']) && isset($data['current_longitude'])) {
      // find the neariest dealer
      if ($data['current_latitude'] && $data['current_longitude']) {
        $centerLatitude = strval($data['current_latitude']);
        $centerLongitude = strval($data['current_longitude']);
        Mage::getSingleton('core/session')->setData('dealer_query', "HERE");
        Mage::getSingleton('core/session')->setData('centerLatitude', $centerLatitude);
        Mage::getSingleton('core/session')->setData('centerLongitude', $centerLongitude);
        $_collection->addExpressionFieldToSelect('distance', '3956 * 2 * ASIN(SQRT( POWER(SIN(({{latitude}} - ' . $centerLatitude . ') *  pi()/180 / 2), 2) +COS({{latitude}} * pi()/180) * COS(' . $centerLatitude . ' * pi()/180) * POWER(SIN(({{longitude}} - ' . $centerLongitude . ') * pi()/180 / 2), 2) ))', array('latitude' => 'latitude', 'longitude' => 'longitude'));
        $_collection->getSelect()->order('distance', 'ASC');
      }
    }
    return $_collection;
  }

  public function getDistancesToDealers() {
    //    $centerLatitude = Mage::getSingleton('core/session')->getData('centerLatitude');
    //    $centerLongitude = Mage::getSingleton('core/session')->getData('centerLongitude');
    //    if ((isset($centerLatitude))
    //      &&(isset($centerLongitude))) {
    //      $dealer_distances = array();
    //      foreach($this->getDealers()->getData() as $item) {
    //        $dealer_distances[$item['dealer_id']] = Mage::helper('dealerlocator')->distanceBetweenTwoCoord($item['latitude'], $item['longitude'], $centerLatitude, $centerLongitude);
    //      }
    //      asort($dealer_distances);
    //      return $dealer_distances;
    //    }
  }

  public function getSearchQueryText() {
    return (string)$this->getRequest()->getParam('q');
  }

  public function getSearchRadius() {
    return (string)$this->getRequest()->getParam('d');
  }

  public function getDefaultLatLong() {
    $helper = Mage::helper('dealerlocator');
    $defaultLocation = $helper->getDefaultAddress();
    $defaultLatLong = array();

    //set default location by address
    if ($defaultLocation != '') {
      $address = urlencode($defaultLocation);
      $json = $helper->getJsonData($address);
      $defaultLatLong = array('lat' => strval($json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'}), 'long' => strval($json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'}));
    }

    $data = $this->getRequest()->getParams();
    if ((isset($data['q']) && $data['q'] != '') || ($defaultLocation == '')) {
      //searching - display default by result
      if ($this->_getDealers()->getSize()) {
        $nearest_dealers = $this->_getDealers()->getFirstItem();
        $defaultLatLong = array('lat' => $nearest_dealers->getLatitude(), 'long' => $nearest_dealers->getLongitude(),);
      }
    }

    if (empty($defaultLatLong)) {
      // if it is empty, set to a default location to avoid problem
      $defaultLatLong = array('lat' => '29.737354', 'long' => '-95.416767');
    }

    return $defaultLatLong;
  }

  public function getPagerHtml() {
    return $this->getChildHtml('pager');
  }

  public function showEmailAndWebsite() {
    return Mage::helper('dealerlocator')->showEmailAndWebsite();
  }

  public function distance($lat1, $lon1, $lat2, $lon2, $unit) {
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
  }

  public function getSearchRadiusUnit() {
    return Mage::helper('dealerlocator')->getRadiusUnit();
  }

  public function getSearchRadiusOptions() {
    return Mage::helper('dealerlocator')->getSearchRadiusOptions();
  }

  /* update version 1.8
  * function getListTag  
  * Return : list Tag 
  */
  public function getListTag() {
    $dealerCollection = $this->_getDealers();
    $dealerIds = $dealerCollection->getColumnValues('dealerlocator_id');
    $tagCollection = Mage::getModel('dealerlocator/tag')->getCollection()->addFieldToFilter('dealer_id', array('in' => $dealerIds));
    $tags = array();
    if (count($tagCollection)) {
      foreach ($tagCollection as $tag) {
        $_newTag = strtolower(trim($tag->getTag()));
        if (!in_array($_newTag, $tags)) {
          $tags[] = $_newTag;
        }
      }
    }
    return $tags;
  }

  public function getSuggestions() {
    $address = urlencode(Mage::getSingleton('core/session')->getData('dealer_query'));
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
    $jsonData = curl_exec($rCURL);
    $data = json_decode($jsonData, TRUE);
    $result = array();
    foreach ($data['results'] as $item) {
      $result[] = $item['formatted_address'];
    }
    // return did you mean from item 2nd
    return array_slice($result, 1, 4);
  }
}