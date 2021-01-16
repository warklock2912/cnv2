<?php
class Magebuzz_Customaddress_Model_Observer {	
	public function addressSaveBefore(Varien_Event_Observer $observer) {
		$address = $observer->getEvent()->getCustomerAddress();
		//$cityId = Mage::app()->getRequest()->getParam('city_id');
		$cityId = $address->getData('city_id');
		$city = $address->getData('city');
		if($cityId && $cityId != 0){
			$city = Mage::getModel('customaddress/city')->load($cityId)->getDefaultName();
		}
		$address->setData('city', $city);
		
		//$subdistrictId = Mage::app()->getRequest()->getParam('subdistrict_id');
		$subdistrictId = $address->getData('subdistrict_id');
		$subdistrict = $address->getData('subdistrict');
		if ($subdistrictId && $subdistrictId != 0) {
			$subdistrict = Mage::getModel('customaddress/subdistrict')->load($subdistrictId)->getDefaultName();
		}
		$address->setData('subdistrict', $subdistrict);

	}
	
	public function addAdditionalDataToAddress(Varien_Event_Observer $observer) {
		$address = $observer->getAddress();
		$regionId = $address->getRegionId();
			
		if ($regionId = $address->getRegionId()) {
			if ($cityId = $address->getCityId()) {
				$json = Mage::helper('customaddress')->getCityJson();
				$data = json_decode($json, true);
				if(isset($data[$regionId][$cityId])) {
					$address->setCity($data[$regionId][$cityId]['name']);
					if($subdistrict = $address->getSubdistrictId()) {
						$json = Mage::helper('customaddress')->getSubDistrictJson();
						$data = json_decode($json, true);
						if(isset($data[$cityId][$subdistrict]) && isset($data[$cityId][$subdistrict]['name'])) {
							$address->setSubdistrict($data[$cityId][$subdistrict]['name']);    				
						}
					}
				}
			}
		}

	}
}