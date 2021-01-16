<?php

class MagentoMasters_MasterCheckout_Block_Checkout_Onepage_Billing extends Mage_Checkout_Block_Onepage_Billing
{

  public function getCountryHtmlSelectNewaddress($type)
  {
    $countryId = $this->getAddress()->getCountryId();
    if (is_null($countryId)) {
      $countryId = Mage::helper('core')->getDefaultCountry();
    }
    $select = $this->getLayout()->createBlock('core/html_select')
      ->setName($type . '[country_id_new]')
      ->setId($type . ':country_id_new')
      ->setTitle(Mage::helper('checkout')->__('Country'))
      ->setClass('validate-select')
      ->setValue($countryId)
      ->setOptions($this->getCountryOptions());
    if ($type === 'shipping') {
      $select->setExtraParams('onchange="if(window.shipping)shipping.setSameAsBilling(false);"');
    }

    return $select->getHtml();
  }

  public function canSetAsDefaultBilling($address)
  {
    if (!$address->getId()) {
      return $this->getCustomerAddressCount();
    }
    return !$this->isDefaultBilling($address);
  }

  public function getCustomerAddressCount()
  {
    return count(Mage::getSingleton('customer/session')->getCustomer()->getAddresses());
  }

  public function isDefaultBilling($address)
  {
    $defaultBilling = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling();
    return $address->getId() && $address->getId() == $defaultBilling;
  }

  public function canSetAsDefaultShipping($address)
  {
    if (!$address->getId()) {
      return $this->getCustomerAddressCount();
    }
    return !$this->isDefaultShipping($address);
  }

  public function isDefaultShipping($address)
  {
    $defaultShipping = Mage::getSingleton('customer/session')->getCustomer()->getDefaultShipping();
    return $address->getId() && $address->getId() == $defaultShipping;
  }

  public function getDefaultShipping()
  {
    $defaultShipping = Mage::getSingleton('customer/session')->getCustomer()->getDefaultShipping();
    if ($defaultShipping) {
      $address = Mage::getModel('customer/address')->load($defaultShipping);
      return $address;
    }
  }
	
	public function getDefaultInputBillingaddress(){
		$inputBillingaddress = array();
		
		$defaultAddress = $this->getCustomer()->getPrimaryBillingAddress();
    if($defaultAddress){
		$defaultAddressHtml = $defaultAddress->format('oneline');
		$addressId = $defaultAddress->getId();
		
		$inputBillingaddress['address_id'] = $addressId;
		$inputBillingaddress['address_html'] = $defaultAddressHtml;
    }
		return $inputBillingaddress;
	}
	
	public function getBillingaddresses(){
		return $this->getCustomer()->getAddresses();
	}
}