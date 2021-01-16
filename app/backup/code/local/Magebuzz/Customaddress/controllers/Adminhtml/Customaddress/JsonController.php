<?php
class Magebuzz_Customaddress_Adminhtml_Customaddress_JsonController extends Mage_Adminhtml_Controller_Action
{
  public function cityAction() {
    
    $arrCity = array();
    $regionId = $this->getRequest()->getParam('parent');
    $arrCities = Mage::getModel('customaddress/city')
      ->getCollection()
      ->addFieldToFilter('region_id', $regionId)
      ->load()
      ->toOptionArray();
    if (!empty($arrCities)) {
      foreach ($arrCities as $city) {
        $arrCity[] = $city;
      }
    }
    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($arrCity));
    
  }

  public function subdistrictAction(){
    
    $arrSubdistrict = array();
    $cityId = $this->getRequest()->getParam('parent');
    $arrAllSub = Mage::getModel('customaddress/subdistrict')
      ->getCollection()
      ->addFieldToFilter('city_id', $cityId)
      ->load()
      ->toOptionArray();
    if (!empty($arrAllSub)) {
      foreach ($arrAllSub as $subdistrict) {
        $arrSubdistrict[] = $subdistrict;
      }
    }
    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($arrSubdistrict));

  }

  public function zipcodeAction(){
    
    $arrZipcode = array();
    $arrAllZip = array();
    $subdistrictId = $this->getRequest()->getParam('parent');
    if (trim($subdistrictId) != "") {
      $arrAllZip = Mage::helper('customaddress')->getZipcodeJson($subdistrictId);
    }
    if (!empty($arrAllZip)) {
      foreach ($arrAllZip as $zipcode) {
        $arrZipcode[] = $zipcode;
      }
    }
    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($arrZipcode));

  }
  
  // use in order edit address
  public function cityOrderAction(){
    $this->getResponse()->setHeader('Content-type', 'application/json');
    $arrCity = array();
    $regionId = $this->getRequest()->getParam('parent');
    $arrCities = Mage::getModel('customaddress/city')
      ->getCollection()
      ->addRegionFilter($regionId)
      ->load()
      ->toOptionArray();
    if (!empty($arrCities)) {
      foreach ($arrCities as $city) {
        $arrCity[] = $city;
      }
    }
    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($arrCity));
    
  }
  
  public function subdistrictOrderAction(){
    $this->getResponse()->setHeader('Content-type', 'application/json');
    $arrSubdistrict = array();
    $cityId = $this->getRequest()->getParam('parent');
    $arrAllSub = Mage::getModel('customaddress/subdistrict')
      ->getCollection()
      ->addCityFilter($cityId)
      ->load()
      ->toOptionArray();
    if (!empty($arrAllSub)) {
      foreach ($arrAllSub as $subdistrict) {
        $arrSubdistrict[] = $subdistrict;
      }
    }
    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($arrSubdistrict));

  }
	
	protected function _isAllowed() {
		return true;
	}
}