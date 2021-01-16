<?php

class Magebuzz_Myaccount_Block_Customer_Address_Edit extends Mage_Directory_Block_Data
{
  protected $_address;
  protected $_countryCollection;
  protected $_regionCollection;

  protected $_id;

//  protected function _prepareLayout()
//  {
//    parent::_prepareLayout();
//    $this->_address = Mage::getModel('customer/address');
//
//    // Init address object
//    if ($id = $this->getRequest()->getParam('id')) {
//      $this->_address->load($id);
//      if ($this->_address->getCustomerId() != Mage::getSingleton('customer/session')->getCustomerId()) {
//        $this->_address->setData(array());
//      }
//    }
//
//    if (!$this->_address->getId()) {
//      $this->_address->setPrefix($this->getCustomer()->getPrefix())
//        ->setFirstname($this->getCustomer()->getFirstname())
//        ->setMiddlename($this->getCustomer()->getMiddlename())
//        ->setLastname($this->getCustomer()->getLastname())
//        ->setSuffix($this->getCustomer()->getSuffix());
//    }
//
//    if ($headBlock = $this->getLayout()->getBlock('head')) {
//      $headBlock->setTitle($this->getTitle());
//    }
//
//    if ($postedData = Mage::getSingleton('customer/session')->getAddressFormData(true)) {
//      $this->_address->addData($postedData);
//    }
//  }

  /**
   * Generate name block html
   *
   * @return string
   */
  public function getNameBlockHtml()
  {
    $nameBlock = $this->getLayout()
      ->createBlock('customer/widget_name')
      ->setObject($this->getAddress());

    return $nameBlock->toHtml();
  }

  public function getTitle()
  {
    if(!$this->_id){
      $this->_id = $this->getRequest()->getParam('id');
      $this->_address = Mage::getModel('customer/address');
      $this->_address->load($this->_id);
    }
    if ($title = $this->getData('title')) {
      return $title;
    }
    if ($this->getAddress()->getId()) {
      $title = Mage::helper('customer')->__('Edit Address');
    }
    else {
      $title = Mage::helper('customer')->__('Add New Address');
    }
    return $title;
  }

  public function getBackUrl()
  {
    if ($this->getData('back_url')) {
      return $this->getData('back_url');
    }

    if ($this->getCustomerAddressCount()) {
      return $this->getUrl('customer/address');
    } else {
      return $this->getUrl('customer/account/');
    }
  }

  public function getSaveUrl()
  {
    return Mage::getUrl('myaccount/index/formPost', array('_secure'=>true, 'id'=>$this->getAddress()->getId()));
  }

  public function getAddress()
  {
    return $this->_address;
  }

  public function getCountryId()
  {
    if ($countryId = $this->getAddress()->getCountryId()) {
      return $countryId;
    }
    return parent::getCountryId();
  }

  public function getRegionId()
  {
    return $this->getAddress()->getRegionId();
  }

  public function getCustomerAddressCount()
  {
    return count(Mage::getSingleton('customer/session')->getCustomer()->getAddresses());
  }

  public function canSetAsDefaultBilling()
  {
    if (!$this->getAddress()->getId()) {
      return $this->getCustomerAddressCount();
    }
    return !$this->isDefaultBilling();
  }

  public function canSetAsDefaultShipping()
  {
    if (!$this->getAddress()->getId()) {
      return $this->getCustomerAddressCount();
    }
    return !$this->isDefaultShipping();;
  }

  public function isDefaultBilling()
  {
    $defaultBilling = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling();
    return $this->getAddress()->getId() && $this->getAddress()->getId() == $defaultBilling;
  }

  public function isDefaultShipping()
  {
    $defaultShipping = Mage::getSingleton('customer/session')->getCustomer()->getDefaultShipping();
    return $this->getAddress()->getId() && $this->getAddress()->getId() == $defaultShipping;
  }

  public function getCustomer()
  {
    return Mage::getSingleton('customer/session')->getCustomer();
  }

  public function getBackButtonUrl()
  {
    if ($this->getCustomerAddressCount()) {
      return $this->getUrl('customer/address');
    } else {
      return $this->getUrl('customer/account/');
    }
  }

  public function getCountryHtmlSelectEdit($defValue=null, $name='country_id', $id='country', $title='Country')
  {
    Varien_Profiler::start('TEST: '.__METHOD__);
    if (is_null($defValue)) {
      $defValue = $this->getCountryId();
    }
    $cacheKey = 'DIRECTORY_COUNTRY_SELECT_STORE_'.Mage::app()->getStore()->getCode();
    if (Mage::app()->useCache('config') && $cache = Mage::app()->loadCache($cacheKey)) {
      $options = unserialize($cache);
    } else {
      $options = $this->getCountryCollection()->toOptionArray();
      if (Mage::app()->useCache('config')) {
        Mage::app()->saveCache(serialize($options), $cacheKey, array('config'));
      }
    }
    $html = $this->getLayout()->createBlock('core/html_select')
      ->setName($name)
      ->setId($id.$this->_id)
      ->setTitle(Mage::helper('directory')->__($title))
      ->setClass('validate-select')
      ->setValue($defValue)
      ->setOptions($options)
      ->getHtml();

    Varien_Profiler::stop('TEST: '.__METHOD__);
    return $html;
  }
}
