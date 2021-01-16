<?php
/*
* Copyright (c) 2015 www.magebuzz.com 
*/
class Magebuzz_Customaddress_Helper_Data extends Mage_Core_Helper_Abstract {
	protected $_cityJson;
	protected $_subdistrictJson;
	
	public function getLocales() {
		$stores = Mage::app()->getStores();
		$locales = array();
		foreach ($stores as $store) {
				$v = Mage::getStoreConfig('general/locale/code', $store->getId());
				$locales[$v] = $v;
		}
		return $locales;
  }
	
	public function getCityJson() {
		Varien_Profiler::start('TEST: '.__METHOD__);
		$storeId = Mage::app()->getStore()->getId();
		if (!$this->_cityJson) {
			$cacheKey = 'CUSTOMADDRESS_CITY_JSON_STORE' . (string)$storeId;
			if (Mage::app()->useCache('config')) {
				$json = Mage::app()->loadCache($cacheKey);
			}
			if (empty($json)) {
				$cities = $this->_getCities($storeId);
				$helper = Mage::helper('core');
				$json = $helper->jsonEncode($cities);

				if (Mage::app()->useCache('config')) {
					Mage::app()->saveCache($json, $cacheKey, array('config'));
				}
			}
			$this->_cityJson = $json;
		}

		Varien_Profiler::stop('TEST: ' . __METHOD__);
		return $this->_cityJson;
	}
	
	public function getSubdistrictJson() {
		Varien_Profiler::start('TEST: '.__METHOD__);
		$storeId = Mage::app()->getStore()->getId();
		if (!$this->_subdistrictJson) {
			$cacheKey = 'CUSTOMADDRESS_SUBDISTRICT_JSON_STORE' . (string)$storeId;
			if (Mage::app()->useCache('config')) {
				$json = Mage::app()->loadCache($cacheKey);
			}
			if (empty($json)) {
				$subdistricts = $this->_getSubdistricts($storeId);
				$helper = Mage::helper('core');
				$json = $helper->jsonEncode($subdistricts);

				if (Mage::app()->useCache('config')) {
					Mage::app()->saveCache($json, $cacheKey, array('config'));
				}
			}
			$this->_subdistrictJson = $json;
		}

		Varien_Profiler::stop('TEST: ' . __METHOD__);
		return $this->_subdistrictJson;
	}		
	
	protected function _getCities($storeId) {
		$cities = array();
		
		$collection = Mage::getSingleton('customaddress/city')->getCollection();
			//->addFieldToFilter('region_id', $regionId); 
	
		foreach ($collection as $item) {
			$itemData = $item->getData();
			$cities[$itemData['region_id']][$itemData['city_id']] = array(
				'code'	=> $itemData['city_id'],
				'name'	=> $this->__($item->getName())
			);
		}
		return $cities;
	}
	
	protected function _getSubdistricts($storeId) {
		$subdistricts = array();
		
		$collection = Mage::getSingleton('customaddress/subdistrict')->getCollection();
			//->addFieldToFilter('region_id', $regionId); 
	
		foreach ($collection as $item) {
			$itemData = $item->getData();
			$subdistricts[$itemData['city_id']][$itemData['subdistrict_id']] = array(
				'code'	=> $itemData['subdistrict_id'],
				'name'	=> $this->__($item->getName()), 
				'zipcode' => $itemData['zipcode']
			);
		}

		return $subdistricts;
	}
	
	// get zipcode admin json - Harry
	public function getZipcodeJson($subdistrictId){
		$collection = Mage::getModel('customaddress/subdistrict')->getResourceCollection()
			->addFieldToFilter('main_table.subdistrict_id', array('in' => array('in' => array($subdistrictId))))
			->load();
		foreach ($collection as $subdistrict) {
			if (!$subdistrict->getSubdistrictId()) {
				continue;
			}
			$arrAllZip[] = array(
				'title' => $subdistrict->getZipcode(),
				'value' => $subdistrict->getZipcode(),
				'label' => $subdistrict->getZipcode()
			);
		}

		return $arrAllZip;
	}
}