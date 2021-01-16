<?php
class MagentoMasters_MasterCheckout_Block_Checkout_Onepage_Shipping extends Mage_Checkout_Block_Onepage_Shipping
{
	protected $_taxvat;
	
	protected function _getTaxvat() {
			if (!$this->_taxvat) {
					$this->_taxvat = $this->getLayout()->createBlock('customer/widget_taxvat');
			}

			return $this->_taxvat;
	}
	
	public function isTaxvatEnabled()
	{
			return $this->_getTaxvat()->isEnabled();
	}
	
	public function getDefaultInputShippingaddress(){
		$inputShippingaddress = array();
		
		$defaultAddress = $this->getCustomer()->getPrimaryShippingAddress();
    if($defaultAddress){
		$defaultAddressHtml = $defaultAddress->format('oneline');
		$addressId = $defaultAddress->getId();
		
		$inputShippingaddress['address_id'] = $addressId;
		$inputShippingaddress['address_html'] = $defaultAddressHtml;
    }
		return $inputShippingaddress;
	}
	
	public function getShippingaddresses(){
		return $this->getCustomer()->getAddresses();
	}
}